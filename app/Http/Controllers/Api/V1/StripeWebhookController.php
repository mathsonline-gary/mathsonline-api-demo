<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\StripeWebhookRequest;
use App\Services\MembershipService;
use App\Services\ProductService;
use App\Services\SchoolService;
use App\Services\StripeService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
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
        protected StripeService       $stripeService,
    )
    {
    }

    public function handle(StripeWebhookRequest $request, int $marketId)
    {
        $request = $request->verify($marketId);

        $payload = json_decode($request->getContent(), true);

        $event = $this->constructEvent($payload);

        // Set the maximum number of retries.
        Stripe::setMaxNetworkRetries(3);

        // Handle the event.
        switch ($event->type) {
            case Event::TYPE_CUSTOMER_SUBSCRIPTION_CREATED:
                $response = $this->handleCustomerSubscriptionCreated($event);
                break;

            case Event::TYPE_CUSTOMER_SUBSCRIPTION_DELETED:
                $response = $this->handleCustomerSubscriptionDeleted($payload);
                break;

            case Event::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED:
                $response = $this->handleCustomerSubscriptionUpdated($event);
                break;

            default:
                return $this->missingMethod();
        }

        return $response;
    }

    protected function handleCustomerSubscriptionCreated(Event $event): JsonResponse
    {
        $stripeSubscription = $event->data['object'];

        if (!$stripeSubscription instanceof StripeSubscription) {
            Log::channel('stripe')
                ->error('[customer.subscription.created] Invalid event data.', $event->toArray());
        }

        // Check if the Stripe customer has a corresponding school in our database.
        if (!($school = $this->schoolService->findByStripeId($stripeSubscription->customer))) {
            Log::channel('stripe')
                ->error('[customer.subscription.created] The associated school not found.', $event->toArray());

            return $this->successMethod('The associated school not found.');
        }

        // Check if the Stripe subscription already exists in our database by Stripe subscription ID.
        if ($school->subscriptions->contains('stripe_id', $stripeSubscription->id)) {
            Log::channel('stripe')
                ->error('[customer.subscription.created] The associated subscription already exists.', $event->toArray());

            return $this->successMethod('The associated subscription already exists.');
        }

        // Check if the Stripe subscription has a corresponding membership in our database.
        $plan = $stripeSubscription->items->first()->plan;

        if (!$membership = $this->membershipService->findByStripeId($plan->id)) {
            Log::channel('stripe')
                ->error('[customer.subscription.created] The associated membership not found.', $event->toArray());

            return $this->successMethod('The associated membership not found.');
        }

        // Otherwise, create a new subscription.
        $this->subscriptionService->create([
            'school_id' => $school->id,
            'membership_id' => $membership->id,
            'stripe_id' => $stripeSubscription->id,
            'starts_at' => $stripeSubscription->start_date,
            'cancels_at' => $stripeSubscription->cancel_at,
            'current_period_starts_at' => $stripeSubscription->current_period_start,
            'current_period_ends_at' => $stripeSubscription->current_period_end,
            'canceled_at' => $stripeSubscription->canceled_at,
            'ended_at' => $stripeSubscription->ended_at,
            'status' => $stripeSubscription->status,
            'custom_user_limit' => null,
        ]);

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): JsonResponse
    {
        $data = $payload['data']['object'];

        // Check if the Stripe subscription has a corresponding subscription in our database.
        if (!($subscription = $this->subscriptionService->search([
            'stripe_id' => $data['id'],
        ])->first())) {
            Log::channel('stripe')
                ->error('[customer.subscription.deleted] The associated subscription not found.', $payload);

            return $this->successMethod('The associated subscription not found.');
        }

        // Cancel the subscription.
        $this->subscriptionService->cancel($subscription, new Carbon($data['canceled_at']));

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionUpdated(Event $event): JsonResponse
    {
        $stripeSubscription = $event->data['object'];

        if (!$stripeSubscription instanceof StripeSubscription) {
            Log::channel('stripe')
                ->error('[customer.subscription.updated] Invalid event data.', $event->toArray());
        }

        // Check if the Stripe customer has a corresponding school in our database.
        if (!($school = $this->schoolService->findByStripeId($stripeSubscription->customer))) {
            Log::channel('stripe')
                ->error('[customer.subscription.updated] The associated school not found.', $event->toArray());

            return $this->successMethod('The associated school not found.');
        }

        // Get the refreshed Stripe subscription resource.
        $this->stripeService->refreshResource($stripeSubscription, $school->market_id);

        // Check if the Stripe subscription has a corresponding membership in our database.
        $plan = $stripeSubscription->items->first()->plan;
        if (!($membership = $this->membershipService->findByStripeId($plan->id))) {
            Log::channel('stripe')
                ->error('[customer.subscription.updated] The associated membership not found.', $event->toArray());

            return $this->successMethod('The associated membership not found.');
        }

        $attributes = [
            'school_id' => $school->id,
            'membership_id' => $membership->id,
            'stripe_id' => $stripeSubscription->id,
            'starts_at' => $stripeSubscription->start_date,
            'cancels_at' => $stripeSubscription->cancel_at,
            'current_period_starts_at' => $stripeSubscription->current_period_start,
            'current_period_ends_at' => $stripeSubscription->current_period_end,
            'canceled_at' => $stripeSubscription->canceled_at,
            'ended_at' => $stripeSubscription->ended_at,
            'status' => $stripeSubscription->status,
        ];

        // Check if the Stripe subscription has a corresponding subscription in our database. If not, create a new one.
        if (
            $subscription = $school->subscriptions()
                ->where([
                    'stripe_id' => $stripeSubscription->id,
                ])->first()
        ) {
            // Update the subscription.
            $this->subscriptionService->update($subscription, $attributes);
        } else {
            $this->subscriptionService->create($attributes);
        }

        return $this->successMethod();
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
     * @param string $message
     * @return JsonResponse
     */
    protected function successMethod(string $message = 'Webhook handled.'): JsonResponse
    {
        return $this->successResponse(
            message: $message,
        );
    }

    /**
     * Respond with "Webhook unhandled" message.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function missingMethod(string $message = 'Webhook unhandled.'): JsonResponse
    {
        return $this->successResponse(
            message: $message,
        );
    }
}
