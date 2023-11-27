<?php

namespace App\Http\Requests;

use App\Models\Market;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\WebhookSignature;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class StripeWebhookRequest extends FormRequest
{
    public function verify(int $marketId): StripeWebhookRequest
    {
        // Verify the market ID.
        if (Market::where('id', $marketId)->doesntExist()) {
            Log::channel('stripe')
                ->error('Invalid webhook endpoint.', $this->toArray());

            throw new AccessDeniedHttpException('Invalid webhook endpoint.');
        }

        // Verify the signature of the Stripe webhook.
        try {
            WebhookSignature::verifyHeader(
                $this->getContent(),
                $this->header('Stripe-Signature'),
                config("services.stripe.$marketId.webhook.secret"),
                config("services.stripe.$marketId.webhook.tolerance")
            );
        } catch (SignatureVerificationException $exception) {
            Log::channel('stripe')
                ->error($exception->getMessage(), $this->toArray());

            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        return $this;
    }
}
