<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Market;
use App\Services\MembershipService;
use App\Services\SchoolService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\WebhookSignature;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected SchoolService       $schoolService,
        protected SubscriptionService $subscriptionService,
        protected MembershipService   $membershipService,
    )
    {
    }

    public function handle(Request $request, int $marketId)
    {
        // Validate $marketId.
        if (Market::where('id', $marketId)->doesntExist()) {
            return $this->errorResponse(message: 'Invalid request.', status: 422);
        }

        // Verify the signature of the Stripe webhook.
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config("services.stripe.{$marketId}.webhook.secret"),
                config("services.stripe.{$marketId}.webhook.tolerance")
            );
        } catch (SignatureVerificationException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        // Get the payload.
        $payload = json_decode($request->getContent(), true);

        // Retrieve the event.
        try {
            $event = Event::constructFrom($payload);
        } catch (UnexpectedValueException $exception) {
            return $this->errorResponse(message: 'Invalid payload.', status: 422);
        }

        // Handle the event.
        switch ($event->type) {
            case 'customer.subscription.created':
                $response = $this->handleCustomerSubscriptionCreated($payload);
                break;
            default:
                return $this->errorResponse(message: 'Unhandled event.');
        }

        return $response;
    }

    protected function handleCustomerSubscriptionCreated(array $payload): JsonResponse
    {
        $data = $payload['data']['object'];

        // Check if the Stripe customer has a corresponding school in our database.
        if (!($school = $this->schoolService->findByStripeId($data['customer']))) {
            return $this->errorResponse(
                message: 'The Stripe customer ID does not exist in our database.',
                status: 404,
            );
        }

        // Check if the Stripe subscription already exists in our database.
        if ($school->subscriptions->contains('stripe_subscription_id', $data['id'])) {
            return $this->errorResponse(
                message: 'The subscription already exists in our database.',
                status: 422,
            );
        }

        // Find the associated membership by the Stripe Plan.
        $plan = $data['items']['data'][0]['plan'];
        if (!$membership = $this->membershipService->findByStripeId($plan['id'])) {
            return $this->errorResponse(
                message: 'The Stripe Plan does not exist in our database.',
                status: 404,
            );
        }
        
        // Create a new subscription.
        $subscription = $this->subscriptionService->create([
            'school_id' => $school->id,
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

        return $this->successResponse(
            data: $subscription->only(['id', 'membership_id']),
            message: 'Subscription created successfully.',
            status: 201,
        );
    }
}
