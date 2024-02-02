<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\StripeWebhookRequest;
use App\Jobs\ProcessStripeSubscriptionWebhookEvent;
use App\Models\Subscription;
use App\Services\MembershipService;
use App\Services\ProductService;
use App\Services\SchoolService;
use App\Services\StripeService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UnexpectedValueException;

/**
 * The controller for handling Stripe webhook events.
 *
 * These events are triggered by making Stripe operations via the Stripe API or directly in the Stripe Dashboard.
 *
 * To avoid API loops, you **MUST NOT** make any Stripe API requests in the webhook handler.
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
            case Event::TYPE_CUSTOMER_SUBSCRIPTION_DELETED:
            case Event::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED:
                ProcessStripeSubscriptionWebhookEvent::dispatch($event, $marketId)->onQueue("stripe-webhook");
                break;

            default:
                return $this->missingMethod();
        }

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionCreated(Event $event): JsonResponse
    {
        $stripeSubscription = $event->data['object'];

        if (!$stripeSubscription instanceof StripeSubscription) {
            return $this->handleEventError($event, 'Invalid event data.');
        }

        $attributes = $this->stripeService->parseSubscriptionAttributes($stripeSubscription);

        // Check if the Stripe customer has an associated school.
        if (is_null($attributes['school'])) {
            return $this->handleEventError($event, 'The associated school not found.');
        }

        // Check if the Stripe subscription already exists.
        if ($attributes['subscription']) {
            return $this->handleEventError($event, 'The associated subscription already exists.');
        }

        // Check if the Stripe subscription has an associated membership with an active campaign.
        if (is_null($attributes['membership'])) {
            return $this->handleEventError($event, 'The associated membership not found.');
        } else if (!$attributes['membership']->campaign->isActive()) {
            return $this->handleEventError($event, 'The associated membership campaign is not active.');
        }

        // Otherwise, create a new subscription.
        $this->subscriptionService->create($attributes);

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionDeleted(Event $event): JsonResponse
    {
        $stripeSubscription = $event->data['object'];

        if (!$stripeSubscription instanceof StripeSubscription) {
            return $this->handleEventError($event, 'Invalid event data.');
        }

        $attributes = $this->stripeService->parseSubscriptionAttributes($stripeSubscription);

        // Check if the Stripe customer has an associated school.
        if (is_null($attributes['school'])) {
            return $this->handleEventError($event, 'The associated school not found.');
        }

        // Check if the Stripe subscription has an associated subscription.
        if ($subscription = $attributes['subscription']) {

            // Skip if the subscription has been canceled.
            if ($subscription->isCanceled()) {
                return $this->handleEventError($event, 'The subscription has been canceled.');
            }

            // Otherwise, update the subscription.
            $this->subscriptionService->update($subscription, $attributes);
        } else {

            // If the subscription doesn't exist, create a canceled subscription.
            if (is_null($attributes['membership'])) {
                return $this->handleEventError($event, 'The associated membership not found.');
            }

            $this->subscriptionService->create($attributes);
        }

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionUpdated(Event $event, int $marketId): JsonResponse
    {
        $stripeSubscription = $event->data['object'];

        if (!$stripeSubscription instanceof StripeSubscription) {
            return $this->handleEventError($event, 'Invalid event data.');
        }

        // Get the refreshed Stripe subscription.
        $this->stripeService->refreshResource($stripeSubscription, $marketId);
        $attributes = $this->stripeService->parseSubscriptionAttributes($stripeSubscription);

        // Check if the Stripe customer has an associated school.
        if (is_null($attributes['school'])) {
            return $this->handleEventError($event, 'The associated school not found.');
        }

        // Check if the Stripe subscription has an associated membership.
        if (is_null($attributes['membership'])) {
            return $this->handleEventError($event, 'The associated membership not found.');
        }

        // Check if the Stripe subscription has an associated subscription.
        if (is_null($subscription = $attributes['subscription'])) {
            // Create a new subscription if it doesn't exist.
            $this->subscriptionService->create($attributes);
        } else {
            // Skip if the subscription has been cancelled.
            if ($subscription->status === Subscription::STATUS_CANCELED) {
                return $this->handleEventError($event, 'The subscription has been canceled.');
            }

            // Otherwise, update the subscription.
            $this->subscriptionService->update($subscription, $attributes);
        }

        return $this->successMethod();
    }

    /**
     * Construct the Stripe event.
     *
     * @param array $payload
     *
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
     *
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
     *
     * @return JsonResponse
     */
    protected function missingMethod(string $message = 'Webhook unhandled.'): JsonResponse
    {
        return $this->successResponse(
            message: $message,
        );
    }

    /**
     * Handle the event error:
     * 1. Log the error.
     * 2. Skip the further processing and respond immediately.
     *
     * @param Event  $event
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function handleEventError(Event $event, string $message): JsonResponse
    {
        Log::channel('stripe')
            ->error("[$event->type] $message", $event->toArray());

        return $this->successMethod($message);
    }
}
