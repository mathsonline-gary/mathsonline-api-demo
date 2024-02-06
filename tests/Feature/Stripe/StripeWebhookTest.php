<?php

namespace Feature\Stripe;

use App\Jobs\ProcessStripeSubscriptionWebhookEvent;
use App\Jobs\ProcessStripeWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Stripe\Event as StripeEvent;
use Stripe\StripeClient;
use Tests\TestCase;

/**
 * To run this test, you need to have the connected Stripe account with "customer.subscription.created" events.
 *
 * You can log in the connected Stripe dashboard and generate those events by managing customers and subscriptions.
 *
 */
class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected StripeClient $stripe;

    protected int $marketId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripe = $this->newStripeClient($this->marketId);
    }

    public function test_it_verifies_stripe_signature(): void
    {
        // Get a "customer.subscription.created" event.
        $payload = $this->stripe->events->all([
            'limit' => 1,
        ])->first()->toArray();

        // Send the event to the webhook.
        $response = $this->postJson(
            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
            $payload,
        );

        // Assert that the response is forbidden.
        $response->assertForbidden();
    }

    public function test_it_handles_customer_subscription_created_event(): void
    {
        Queue::fake([ProcessStripeWebhookEvent::class]);

        // Get a "customer.subscription.created" event.
        $payload = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_CREATED,
            'limit' => 1,
        ])->first()->toArray();

        // Send the event to the webhook.
        $response = $this->postJson(
            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
            $payload,
            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
        );

        // Assert that the webhook was handled successfully.
        $response->assertStripeWebhookSuccessful();

        // Assert that the job was dispatched.
        Queue::assertPushedOn(ProcessStripeWebhookEvent::QUEUE_NAME, ProcessStripeSubscriptionWebhookEvent::class);
    }

    public function test_it_handles_customer_subscription_updated_event(): void
    {
        Queue::fake([ProcessStripeWebhookEvent::class]);

        // Get a "customer.subscription.updated" event.
        $payload = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED,
            'limit' => 1,
        ])->first()->toArray();

        // Send the event to the webhook.
        $response = $this->postJson(
            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
            $payload,
            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
        );

        // Assert that the webhook was handled successfully.
        $response->assertStripeWebhookSuccessful();

        // Assert that the job was dispatched.
        Queue::assertPushedOn(ProcessStripeWebhookEvent::QUEUE_NAME, ProcessStripeSubscriptionWebhookEvent::class);
    }

    public function test_it_handles_customer_subscription_deleted_event(): void
    {
        Queue::fake([ProcessStripeWebhookEvent::class]);

        // Get a "customer.subscription.deleted" event.
        $payload = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_DELETED,
            'limit' => 1,
        ])->first()->toArray();

        // Send the event to the webhook.
        $response = $this->postJson(
            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
            $payload,
            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
        );

        // Assert that the webhook was handled successfully.
        $response->assertStripeWebhookSuccessful();

        // Assert that the job was dispatched.
        Queue::assertPushedOn(ProcessStripeWebhookEvent::QUEUE_NAME, ProcessStripeSubscriptionWebhookEvent::class);
    }
}
