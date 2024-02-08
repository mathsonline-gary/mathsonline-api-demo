<?php

namespace Tests\Helpers;

use Stripe\StripeClient;

trait StripeTestHelpers
{
    /**
     * Create a new Stripe client for the given market.
     *
     * @param int $marketId
     *
     * @return StripeClient
     */
    public function newStripeClient(int $marketId): StripeClient
    {
        return new StripeClient(config("services.stripe.$marketId.secret"));
    }

    /**
     * Generate a Stripe signature for the given payload.
     *
     * @param int   $marketId
     * @param array $payload
     *
     * @return string
     */
    public function generateStripeSignature(int $marketId, array $payload): string
    {
        $webhookSecret = config("services.stripe.$marketId.webhook.secret");

        $timestamp = time();
        $payloadJson = json_encode($payload);
        $secret = $webhookSecret;

        $signature = hash_hmac('sha256', "{$timestamp}.{$payloadJson}", $secret);

        return "t={$timestamp},v1={$signature}";
    }

}
