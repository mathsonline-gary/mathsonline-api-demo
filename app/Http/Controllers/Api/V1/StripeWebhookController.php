<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Market;
use App\Services\SchoolService;
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
        protected SchoolService $schoolService,
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
        if ($school = $this->schoolService->findByStripeId($payload['data']['object']['customer'])) {
            // Update the school's subscription status.
        }

        return $this->successResponse(data: $payload);
    }
}
