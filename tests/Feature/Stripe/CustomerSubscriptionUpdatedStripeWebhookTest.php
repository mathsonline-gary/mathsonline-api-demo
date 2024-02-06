<?php

namespace Tests\Feature\Stripe;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\StripeClient;
use Tests\TestCase;

/**
 * To run this test, you need to have the connected Stripe account with "customer.subscription.updated" events.
 *
 * You can log in the connected Stripe dashboard and generate those events by managing customers and subscriptions.
 *
 */
class CustomerSubscriptionUpdatedStripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected StripeClient $stripe;

    protected int $marketId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripe = $this->newStripeClient($this->marketId);
    }

//    public function test_it_skips_if_no_school_connects_to_the_updated_stripe_subscription(): void
//    {
//        {
//            // Assert that there are no school or subscription records.
//            $this->assertDatabaseCount('schools', 0);
//            $this->assertDatabaseCount('subscriptions', 0);
//
//            // Get a "customer.subscription.updated" event.
//            $payload = $this->stripe->events->all([
//                'type' => 'customer.subscription.updated',
//                'limit' => 1,
//            ])->data[0]->toArray();
//        }
//
//        // Send the event to the webhook.
//        $response = $this->postJson(
//            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
//            $payload,
//            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
//        );
//
//        // Assert that the webhook was handled successfully.
//        $response->assertStripeWebhookSuccessful('The associated school not found.');
//
//        // Assert that there are no school or subscription records created.
//        $this->assertDatabaseCount('schools', 0);
//        $this->assertDatabaseCount('subscriptions', 0);
//    }
//
//    public function test_it_skips_if_no_membership_matches_the_price_of_the_updated_subscription()
//    {
//        {
//            // Get a "customer.subscription.created" event.
//            $payload = $this->stripe->events->all([
//                'type' => 'customer.subscription.updated',
//                'limit' => 1,
//            ])->data[0]->toArray();
//
//            // Fake the related school.
//            $school = $this->fakeHomeschool(1, [
//                'market_id' => $this->marketId,
//                'stripe_id' => $payload['data']['object']['customer']
//            ]);
//
//            // Remove all memberships.
//            Membership::whereNotNull('id')->delete();
//
//            // Assert that the school already exists.
//            $this->assertDatabaseCount('schools', 1);
//            $this->assertDatabaseHas('schools', ['stripe_id' => $payload['data']['object']['customer']]);
//
//            // Assert that there are no  memberships.
//            $this->assertDatabaseCount('memberships', 0);
//        }
//
//        // Send the event to the webhook.
//        $response = $this->postJson(
//            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
//            $payload,
//            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
//        );
//
//        // Assert that the webhook was handled successfully.
//        $response->assertStripeWebhookSuccessful('The associated membership not found.');
//
//        // Assert that the school was neither created nor updated.
//        $this->assertDatabaseCount('schools', 1);
//        $this->assertSchoolAttributes($school->getAttributes(), School::first());
//
//        // Assert that the subscription was neither created nor updated.
//        $this->assertDatabaseCount('subscriptions', 0);
//    }
//
//    public function test_it_skips_if_the_corresponding_subscription_is_canceled()
//    {
//        {
//            // Get a "customer.subscription.updated" event.
//            $payload = $this->stripe->events->all([
//                'type' => 'customer.subscription.updated',
//                'limit' => 1,
//            ])->data[0]->toArray();
//
//            $stripeSubscription = $payload['data']['object'];
//            $stripePriceId = $stripeSubscription['items']['data'][0]['price']['id'];
//
//            // Fake the related school.
//            $school = $this->fakeHomeschool(1, [
//                'market_id' => $this->marketId,
//                'stripe_id' => $stripeSubscription['customer']
//            ]);
//
//            // Fake the related membership if it doesn't exist.
//            if (!Membership::where('stripe_id', $stripePriceId)->exists()) {
//                Membership::factory()->create([
//                    'stripe_id' => $stripePriceId,
//                ]);
//            }
//
//            // Fake the related subscription that is canceled.
//            $subscription = $this->fakeSubscription(
//                $school,
//                Subscription::STATUS_ACTIVE,
//                Membership::where('stripe_id', $stripePriceId)->first()
//            );
//            $subscription->stripe_id = $stripeSubscription['id'];
//            $subscription->canceled_at = now();
//            $subscription->status = Subscription::STATUS_CANCELED;
//            $subscription->save();
//
//            // Assert that the school already exists.
//            $this->assertDatabaseCount('schools', 1);
//            $this->assertDatabaseHas('schools', ['stripe_id' => $stripeSubscription['customer']]);
//
//            // Assert that the membership already exists.
//            $this->assertDatabaseHas('memberships', ['stripe_id' => $stripePriceId]);
//
//            // Assert that the subscription already exists.
//            $this->assertDatabaseCount('subscriptions', 1);
//        }
//
//        // Send the event to the webhook.
//        $response = $this->postJson(
//            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
//            $payload,
//            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
//        );
//
//        // Assert that the webhook was handled successfully.
//        $response->assertStripeWebhookSuccessful('The subscription has been canceled.');
//
//        // Assert that the school was neither created nor updated.
//        $this->assertDatabaseCount('schools', 1);
//        $this->assertSchoolAttributes($school->getAttributes(), School::first());
//
//        // Assert that no new subscription was created.
//        $this->assertDatabaseCount('subscriptions', 1);
//
//        // Assert that the subscription was not updated.
//        $this->assertSubscriptionAttributes([
//            'stripe_id' => $subscription->stripe_id,
//            'status' => Subscription::STATUS_CANCELED,
//            'starts_at' => $subscription->starts_at,
//            'cancels_at' => $subscription->cancels_at,
//            'current_period_starts_at' => $subscription->current_period_starts_at,
//            'current_period_ends_at' => $subscription->current_period_ends_at,
//            'canceled_at' => $subscription->canceled_at,
//            'school_id' => $school->id,
//        ], Subscription::first());
//    }
//
//    public function test_it_updates_the_subscription_if_it_exists(): void
//    {
//        {
//            // Get a "customer.subscription.updated" event.
//            $payload = $this->stripe->events->all([
//                'type' => 'customer.subscription.updated',
//                'limit' => 1,
//            ])->data[0]->toArray();
//
//            $stripeSubscription = $payload['data']['object'];
//            $stripePriceId = $stripeSubscription['items']['data'][0]['price']['id'];
//
//            // Fake the related school.
//            $school = $this->fakeHomeschool(1, [
//                'market_id' => $this->marketId,
//                'stripe_id' => $stripeSubscription['customer']
//            ]);
//
//            // Fake the related membership if it doesn't exist.
//            if (!Membership::where('stripe_id', $stripePriceId)->exists()) {
//                Membership::factory()->create([
//                    'stripe_id' => $stripePriceId,
//                ]);
//            }
//
//            // Fake the related subscription.
//            $subscription = $this->fakeSubscription(
//                $school,
//                Subscription::STATUS_ACTIVE,
//                Membership::where('stripe_id', $stripePriceId)->first()
//            );
//            $subscription->stripe_id = $stripeSubscription['id'];
//            $subscription->save();
//
//            // Assert that the school already exists.
//            $this->assertDatabaseCount('schools', 1);
//            $this->assertDatabaseHas('schools', ['stripe_id' => $stripeSubscription['customer']]);
//
//            // Assert that the membership already exists.
//            $this->assertDatabaseHas('memberships', ['stripe_id' => $stripePriceId]);
//
//            // Assert that the subscription already exists.
//            $this->assertDatabaseCount('subscriptions', 1);
//        }
//
//        // Send the event to the webhook.
//        $response = $this->postJson(
//            route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
//            $payload,
//            ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
//        );
//
//        // Assert that the webhook was handled successfully.
//        $response->assertStripeWebhookSuccessful();
//
//        // Assert that the school was neither created nor updated.
//        $this->assertDatabaseCount('schools', 1);
//        $this->assertSchoolAttributes($school->getAttributes(), School::first());
//
//        // Assert that no new subscription was created.
//        $this->assertDatabaseCount('subscriptions', 1);
//
//        // Assert that the subscription was updated to the latest values.
//        $stripeSubscription = $this->stripe->subscriptions->retrieve($stripeSubscription['id']);
//        $this->assertSubscriptionAttributes([
//            'stripe_id' => $stripeSubscription->id,
//            'status' => $stripeSubscription->status,
//            'starts_at' => $stripeSubscription->start_date,
//            'cancels_at' => $stripeSubscription->cancel_at,
//            'current_period_starts_at' => $stripeSubscription->current_period_start,
//            'current_period_ends_at' => $stripeSubscription->current_period_end,
//            'canceled_at' => $stripeSubscription->canceled_at,
//            'school_id' => $school->id,
//            'membership_id' => Membership::where('stripe_id', $stripeSubscription->items->first()->price->id)->first()->id,
//        ], Subscription::first());
//    }
//
//    public function test_it_creates_a_subscription_if_the_updated_subscription_does_not_exist()
//    {
//        {
//            // Get a "customer.subscription.updated" event.
//            $payload = $this->stripe->events->all([
//                'type' => 'customer.subscription.updated',
//                'limit' => 1,
//            ])->data[0]->toArray();
//
//            $stripeSubscription = $payload['data']['object'];
//            $stripePriceId = $stripeSubscription['items']['data'][0]['price']['id'];
//
//            // Fake the related school.
//            $school = $this->fakeHomeschool(1, [
//                'market_id' => $this->marketId,
//                'stripe_id' => $stripeSubscription['customer']
//            ]);
//
//            // Fake the related membership if it doesn't exist.
//            if (!Membership::where('stripe_id', $stripePriceId)->exists()) {
//                Membership::factory()->create([
//                    'stripe_id' => $stripePriceId,
//                ]);
//            }
//
//            // Assert that the school already exists.
//            $this->assertDatabaseCount('schools', 1);
//            $this->assertDatabaseHas('schools', ['stripe_id' => $stripeSubscription['customer']]);
//
//            // Assert that the membership already exists.
//            $this->assertDatabaseHas('memberships', ['stripe_id' => $stripePriceId]);
//
//            // Assert that the subscription does not exist.
//            $this->assertDatabaseCount('subscriptions', 0);
//
//            // Send the event to the webhook.
//            $response = $this->postJson(
//                route('api.v1.stripe.webhook.handle', ['marketId' => $this->marketId]),
//                $payload,
//                ['Stripe-Signature' => $this->generateStripeSignature($this->marketId, $payload)],
//            );
//        }
//
//        // Assert that the webhook was handled successfully.
//        $response->assertStripeWebhookSuccessful();
//
//        // Assert that the school was neither created nor updated.
//        $this->assertDatabaseCount('schools', 1);
//        $this->assertSchoolAttributes($school->getAttributes(), School::first());
//
//        // Assert that the subscription was created.
//        $this->assertDatabaseCount('subscriptions', 1);
//
//        // Assert that the subscription was updated to the latest values.
//        $stripeSubscription = $this->stripe->subscriptions->retrieve($stripeSubscription['id']);
//        $this->assertSubscriptionAttributes([
//            'stripe_id' => $stripeSubscription->id,
//            'status' => $stripeSubscription->status,
//            'starts_at' => $stripeSubscription->start_date,
//            'cancels_at' => $stripeSubscription->cancel_at,
//            'current_period_starts_at' => $stripeSubscription->current_period_start,
//            'current_period_ends_at' => $stripeSubscription->current_period_end,
//            'canceled_at' => $stripeSubscription->canceled_at,
//            'school_id' => $school->id,
//            'membership_id' => Membership::where('stripe_id', $stripeSubscription->items->first()->price->id)->first()->id,
//        ], Subscription::first());
//    }
}
