<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Market;
use App\Services\MembershipService;
use App\Services\ProductService;
use App\Services\SchoolService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\WebhookSignature;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UnexpectedValueException;

/**
 * The controller for handling Stripe webhook events.
 * These events are triggered by making Stripe operations via the Stripe API or directly in the Stripe Dashboard.
 * To avoid API loops, we handle these events by assuming they are triggered by the Stripe Dashboard.
 *
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        protected SchoolService       $schoolService,
        protected SubscriptionService $subscriptionService,
        protected MembershipService   $membershipService,
        protected ProductService      $productService,
    )
    {
    }

    public function handle(Request $request, int $marketId)
    {
        $request = $this->validateRequest($request, $marketId);

        $payload = json_decode($request->getContent(), true);

        $event = $this->constructEvent($payload);

        // Set the maximum number of retries.
        Stripe::setMaxNetworkRetries(3);

        // Handle the event.
        switch ($event->type) {
            case 'customer.subscription.created':
                $response = $this->handleCustomerSubscriptionCreated($payload);
                break;

            case 'customer.subscription.deleted':
                $response = $this->handleCustomerSubscriptionDeleted($payload);
                break;

            case 'customer.subscription.updated':
                $response = $this->handleCustomerSubscriptionUpdated($payload);
                break;

            default:
                return $this->missingMethod();
        }

        return $response;
    }

    protected function handleCustomerSubscriptionCreated(array $payload): JsonResponse
    {
        $data = $payload['data']['object'];

        // Check if the Stripe customer has a corresponding school in our database.
        if (!($school = $this->schoolService->findByStripeId($data['customer']))) {
            Log::channel('stripe')
                ->error('[customer.subscription.created] The associated school not found.', $payload);

            return $this->missingMethod();
        }

        // Check if the Stripe subscription already exists in our database.
        if ($school->subscriptions->contains('stripe_subscription_id', $data['id'])) {
            Log::channel('stripe')
                ->error('[customer.subscription.created] The associated subscription already exists.', $payload);

            return $this->missingMethod();
        }

        // Check if the Stripe subscription has a corresponding membership in our database.
        $plan = $data['items']['data'][0]['plan'];
        if (!$membership = $this->membershipService->findByStripeId($plan['id'])) {
            Log::channel('stripe')
                ->error('[customer.subscription.created] The associated membership not found.', $payload);

            return $this->missingMethod();
        }

        // Create a new subscription.
        $this->subscriptionService->create([
            'school_id' => $school->id,
            'product_id' => $membership->product->id,
            'membership_id' => $membership->id,
            'stripe_subscription_id' => $data['id'],
            'starts_at' => $data['start_date'],
            'cancels_at' => $data['cancel_at'],
            'canceled_at' => $data['canceled_at'],
            'ended_at' => $data['ended_at'],
            'status' => $data['status'],
            'custom_price' => null,
            'custom_user_limit' => null,
        ]);

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): JsonResponse
    {
        $data = $payload['data']['object'];

        // Check if the Stripe customer has a corresponding school in our database.
        if (!($school = $this->schoolService->findByStripeId($data['customer']))) {
            Log::channel('stripe')
                ->error('[customer.subscription.deleted] The associated school not found.', $payload);

            return $this->missingMethod();
        }

        // Check if the Stripe subscription has a corresponding subscription in our database.
        if (!($subscription = $this->subscriptionService->search([
            'school_id' => $school->id,
            'stripe_subscription_id' => $data['id'],
        ])->first())) {
            Log::channel('stripe')
                ->error('[customer.subscription.deleted] The associated subscription not found.', $payload);

            return $this->missingMethod();
        }

        // Cancel the subscription.
        $this->subscriptionService->cancel($subscription, new Carbon($data['canceled_at']));

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionUpdated(array $payload): JsonResponse
    {
        $data = $payload['data']['object'];

        // Check if the Stripe customer has a corresponding school in our database.
        if (!($school = $this->schoolService->findByStripeId($data['customer']))) {
            Log::channel('stripe')
                ->error('[customer.subscription.updated] The associated school not found.', $payload);

            return $this->missingMethod();
        }

        // Check if the Stripe subscription has a corresponding subscription in our database. If not, create a new one.
        $subscription = $school->subscriptions()->firstOrNew([
            'stripe_subscription_id' => $data['id'],
        ]);

        // Set the subscription attributes.
        $attributes = [
            'school_id' => $school->id,
            'stripe_subscription_id' => $data['id'],
            'starts_at' => $data['start_date'],
            'cancels_at' => $data['cancel_at'],
            'canceled_at' => $data['canceled_at'],
            'ended_at' => $data['ended_at'],
            'status' => $data['status'],
        ];

        // Set membership attributes.
        $plan = $data['items']['data'][0]['price'];
        if ($membership = $this->membershipService->findByStripeId($plan['id'])) {
            $attributes['membership_id'] = $membership->id;
            $attributes['product_id'] = $membership->product->id;
            $attributes['custom_price'] = null;
        } else {
            // If the membership is not found, Check the product.
            if (!($product = $this->productService->findByStripeId($plan['product']))) {
                // If the product is not found, log the error and skip the webhook.
                Log::channel('stripe')
                    ->error('[customer.subscription.updated] The associated product not found.', $payload);

                return $this->missingMethod();
            }

            // Otherwise, set the custom price.
            $attributes['membership_id'] = null;
            $attributes['product_id'] = $product->id;
            $attributes['custom_price'] = $plan['unit_amount'] / 100;
        }

        // Update the subscription.
        $this->subscriptionService->update($subscription, $attributes);

        return $this->successMethod();
    }

    /**
     * Validate the webhook request.
     *
     * @param Request $request
     * @param int $marketId
     * @return Request
     */
    protected function validateRequest(Request $request, int $marketId): Request
    {
        // Validate $marketId.
        if (Market::where('id', $marketId)->doesntExist()) {
            Log::channel('stripe')
                ->error('Invalid webhook endpoint.', $request->toArray());

            throw new AccessDeniedHttpException('Invalid webhook endpoint.');
        }

        // Verify the signature of the Stripe webhook.
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config("services.stripe.$marketId.webhook.secret"),
                config("services.stripe.$marketId.webhook.tolerance")
            );
        } catch (SignatureVerificationException $exception) {
            Log::channel('stripe')
                ->error($exception->getMessage(), $request->toArray());

            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        return $request;
    }

    /**
     * Construct the Stripe event.
     *
     * @param array $payload
     * @return Event
     */
    protected function constructEvent(array $payload): Event
    {
        // Construct the Stripe event.
        try {
            return Event::constructFrom($payload);
        } catch (UnexpectedValueException $exception) {
            Log::channel('stripe')
                ->error($exception->getMessage(), $payload);

            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }
    }

    /**
     * Respond with "Webhook handled" message.
     *
     * @return JsonResponse
     */
    protected function successMethod(): JsonResponse
    {
        return $this->successResponse(
            message: 'Webhook handled.',
        );
    }

    /**
     * Respond with "Webhook unhandled" message.
     *
     * @return JsonResponse
     */
    protected function missingMethod(): JsonResponse
    {
        return $this->successResponse(
            message: 'Webhook unhandled.',
        );
    }
}
