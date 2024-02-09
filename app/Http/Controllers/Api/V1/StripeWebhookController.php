<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\StripeWebhookRequest;
use App\Jobs\ProcessStripeSubscriptionWebhookEvent;
use App\Services\MembershipService;
use App\Services\ProductService;
use App\Services\SchoolService;
use App\Services\StripeService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Stripe;
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
            case Event::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED:
            case Event::TYPE_CUSTOMER_SUBSCRIPTION_DELETED:
                ProcessStripeSubscriptionWebhookEvent::dispatch($event, $marketId);
                break;

            default:
                return $this->missingMethod();
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
}
