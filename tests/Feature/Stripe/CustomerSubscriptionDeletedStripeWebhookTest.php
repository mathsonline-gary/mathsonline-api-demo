<?php

namespace Tests\Feature\Stripe;

use App\Enums\SubscriptionStatus;
use App\Models\Membership;
use App\Models\School;
use App\Models\Subscription;
use Stripe\StripeClient;
use Tests\TestCase;

/**
 * To run this test, you need to have the connected Stripe account with "customer.subscription.deleted" events.
 *
 * You can log in the connected Stripe dashboard and generate those events by managing customers and subscriptions.
 *
 */
class CustomerSubscriptionDeletedStripeWebhookTest extends TestCase
{
    protected StripeClient $stripe;

    protected int $marketId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripe = $this->newStripeClient($this->marketId);
    }

    public function test_it_skips_if_no_school_connects_to_the_deleted_stripe_subscription(): void
    {
        // Assert that there are no school or subscription records.
        $this->assertDatabaseCount('schools', 0);
        $this->assertDatabaseCount('subscriptions', 0);

        // Get a "customer.subscription.deleted" event.
        $payload = $this->stripe->events->all([
            'type' => 'customer.subscription.deleted',
            'limit' => 1,
        ])->data[0]->toArray();

        // Send the event to the webhook.
        $response = $this->postJson(
            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
            $payload,
            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
        );

        // Assert that the webhook was handled successfully.
        $response->assertStripeWebhookSuccessful('The associated school not found.');

        // Assert that there are no school or subscription records created.
        $this->assertDatabaseCount('schools', 0);
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_it_skips_if_the_associated_subscription_has_been_canceled(): void
    {
        // Get a "customer.subscription.deleted" event.
        $payload = $this->stripe->events->all([
            'type' => 'customer.subscription.deleted',
            'limit' => 1,
        ])->data[0]->toArray();

        $stripeSubscription = $payload['data']['object'];
        $stripePriceId = $stripeSubscription['items']['data'][0]['price']['id'];

        // Fake the related school.
        $school = $this->fakeHomeschool(1, [
            'market_id' => $this->marketId,
            'stripe_id' => $stripeSubscription['customer']
        ]);

        // Fake the related membership if it doesn't exist.
        if (!$membership = Membership::where('stripe_id', $stripePriceId)->exists()) {
            $membership = Membership::factory()->create([
                'stripe_id' => $stripePriceId,
            ]);
        }

        // Fake the existing subscription.
        $subscription = $this->fakeSubscription($school, SubscriptionStatus::CANCELED, $membership);
        $subscription->stripe_id = $stripeSubscription['id'];
        $subscription->save();

        // Assert that the school already exists.
        $this->assertDatabaseCount('schools', 1);
        $this->assertDatabaseHas('schools', ['stripe_id' => $stripeSubscription['customer']]);

        // Assert that the membership already exists.
        $this->assertDatabaseHas('memberships', ['stripe_id' => $stripePriceId]);

        // Assert that the subscriptions already exists.
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', ['stripe_id' => $stripeSubscription['id']]);

        // Send the event to the webhook.
        $response = $this->postJson(
            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
            $payload,
            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
        );

        // Assert that the webhook was handled successfully.
        $response->assertStripeWebhookSuccessful('The subscription has been canceled.');
    }

    public function test_it_creates_a_canceled_subscription_if_no_subscription_connects_to_the_deleted_stripe_subscription(): void
    {
        // Get a "customer.subscription.deleted" event.
        $payload = $this->stripe->events->all([
            'type' => 'customer.subscription.deleted',
            'limit' => 1,
        ])->data[0]->toArray();

        $stripeSubscription = $payload['data']['object'];
        $stripePriceId = $stripeSubscription['items']['data'][0]['price']['id'];

        // Fake the related school.
        $school = $this->fakeHomeschool(1, [
            'market_id' => $this->marketId,
            'stripe_id' => $stripeSubscription['customer']
        ]);

        // Fake the related membership if it doesn't exist.
        if (!Membership::where('stripe_id', $stripePriceId)->exists()) {
            Membership::factory()->create([
                'stripe_id' => $stripePriceId,
            ]);
        }

        // Assert that the school already exists.
        $this->assertDatabaseCount('schools', 1);
        $this->assertDatabaseHas('schools', ['stripe_id' => $stripeSubscription['customer']]);

        // Assert that the membership already exists.
        $this->assertDatabaseHas('memberships', ['stripe_id' => $stripePriceId]);

        // Assert that there are no subscriptions.
        $this->assertDatabaseCount('subscriptions', 0);

        // Send the event to the webhook.
        $response = $this->postJson(
            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
            $payload,
            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
        );

        // Assert that the webhook was handled successfully.
        $response->assertStripeWebhookSuccessful();

        // Assert that the school was neither created nor updated.
        $this->assertDatabaseCount('schools', 1);
        $this->assertSchoolAttributes($school->getAttributes(), School::first());

        // Assert that the subscription was created.
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertSubscriptionAttributes([
            'stripe_id' => $stripeSubscription['id'],
            'status' => $stripeSubscription['status'],
            'starts_at' => $stripeSubscription['start_date'],
            'cancels_at' => $stripeSubscription['cancel_at'],
            'current_period_starts_at' => $stripeSubscription['current_period_start'],
            'current_period_ends_at' => $stripeSubscription['current_period_end'],
            'canceled_at' => $stripeSubscription['canceled_at'],
            'school_id' => $school->id,
            'membership_id' => Membership::where('stripe_id', $stripePriceId)->first()->id,
        ], Subscription::first());
    }

    public function test_it_cancels_the_existing_subscription(): void
    {
        // Get a "customer.subscription.deleted" event.
        $payload = $this->stripe->events->all([
            'type' => 'customer.subscription.deleted',
            'limit' => 1,
        ])->data[0]->toArray();

        $stripeSubscription = $payload['data']['object'];
        $stripePriceId = $stripeSubscription['items']['data'][0]['price']['id'];

        // Fake the related school.
        $school = $this->fakeHomeschool(1, [
            'market_id' => $this->marketId,
            'stripe_id' => $stripeSubscription['customer']
        ]);

        // Fake the related membership if it doesn't exist.
        if (!$membership = Membership::where('stripe_id', $stripePriceId)->exists()) {
            $membership = Membership::factory()->create([
                'stripe_id' => $stripePriceId,
            ]);
        }

        // Fake the existing subscription.
        $subscription = $this->fakeSubscription($school, SubscriptionStatus::ACTIVE, $membership);
        $subscription->stripe_id = $stripeSubscription['id'];
        $subscription->save();

        // Assert that the school already exists.
        $this->assertDatabaseCount('schools', 1);
        $this->assertDatabaseHas('schools', ['stripe_id' => $stripeSubscription['customer']]);

        // Assert that the membership already exists.
        $this->assertDatabaseHas('memberships', ['stripe_id' => $stripePriceId]);

        // Assert that the subscriptions already exists.
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseHas('subscriptions', ['stripe_id' => $stripeSubscription['id']]);

        // Send the event to the webhook.
        $response = $this->postJson(
            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
            $payload,
            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
        );

        // Assert that the webhook was handled successfully.
        $response->assertStripeWebhookSuccessful();

        // Assert that the school was neither created nor updated.
        $this->assertDatabaseCount('schools', 1);
        $this->assertSchoolAttributes($school->getAttributes(), School::first());

        // Assert that the subscription was created.
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertSubscriptionAttributes([
            'stripe_id' => $stripeSubscription['id'],
            'status' => SubscriptionStatus::CANCELED,
            'starts_at' => $stripeSubscription['start_date'],
            'cancels_at' => $stripeSubscription['cancel_at'],
            'current_period_starts_at' => $stripeSubscription['current_period_start'],
            'current_period_ends_at' => $stripeSubscription['current_period_end'],
            'canceled_at' => $stripeSubscription['canceled_at'],
            'school_id' => $school->id,
            'membership_id' => Membership::where('stripe_id', $stripePriceId)->first()->id,
        ], Subscription::first());
    }
}
