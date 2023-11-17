<?php

namespace Tests\Feature\Subscriptions;

use App\Enums\SubscriptionStatus;
use App\Models\Membership;
use App\Models\Subscription;
use Stripe\StripeClient;
use Tests\TestCase;

class CreateSubscriptionTest extends TestCase
{
    public function test_a_guest_is_unauthenticated_to_subscribe_a_membership()
    {
        $this->assertGuest();

        $response = $this->postJson(route('api.v1.subscriptions.store'), [
            'membership_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_a_member_cannot_subscribe_to_a_new_membership_without_email_verified()
    {
        $member = $this->fakeMember();

        // Set the member's email_verified_at to null.
        $user = $member->asUser();
        $user->email_verified_at = null;
        $user->save();

        $this->actingAsMember($member);

        $response = $this->postJson(route('api.v1.subscriptions.store'), [
            'membership_id' => 1,
            'payment_token_id' => 'tok_mastercard',
        ]);

        // Assert that it responds the email verification is required.
        $response->assertEmailVerificationRequired();
    }

    public function test_a_member_is_unauthorized_to_subscribe_to_a_new_membership_if_he_already_has_an_active_subscription()
    {
        $member = $this->fakeMember();

        // Create a fake active subscription for the member.
        $this->fakeSubscription($member->school);

        $this->actingAsMember($member);

        $response = $this->postJson(route('api.v1.subscriptions.store'), [
            'membership_id' => 1,
            'payment_token_id' => 'tok_mastercard',
        ]);

        $response->assertForbidden();
    }

    public function test_a_member_can_subscribe_a_twelve_month_membership()
    {
        $member = $this->fakeMember();

        $marketId = $member->school->market->id;
        $stripeClient = new StripeClient(config("services.stripe.$marketId.secret"));

        $this->actingAsMember($member);

        // Prepare a valid membership.
        $membership = Membership::whereHas('product', function ($query) use ($member) {
            $query->where('market_id', $member->school->market_id);
        })
            ->whereHas('campaign', function ($query) {
                $query->whereNotNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where('is_recurring', false)
            ->where('period_in_months', 12)
            ->get()
            ->random();

        $response = $this->postJson(route('api.v1.subscriptions.store'), [
            'membership_id' => $membership->id,
            'payment_token_id' => 'tok_mastercard',
        ]);

        $response->assertCreated()
            ->assertJsonSuccess();

        // Assert that the subscription was created in the database.
        $this->assertDatabaseCount('subscriptions', 1);
        $subscription = Subscription::first();
        $this->assertEquals($member->school->id, $subscription->school_id);
        $this->assertEquals($membership->id, $subscription->membership_id);
        $this->assertNotNull($subscription->stripe_id);
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertNotNull($subscription->starts_at);
        $this->assertEquals($subscription->starts_at->addMonths(12), $subscription->cancels_at);
        $this->assertNotNull($subscription->current_period_starts_at);
        $this->assertNotNull($subscription->current_period_ends_at);
        $this->assertNotNull($subscription->canceled_at);
        $this->assertNull($subscription->ended_at);
        $this->assertNull($subscription->custom_user_limit);

        // Assert that the subscription was created in Stripe.
        $stripeSubscription = $stripeClient->subscriptions->retrieve($subscription->stripe_id);

        $this->assertEquals($subscription->stripe_id, $stripeSubscription->id);
        $this->assertEquals($member->school->stripe_id, $stripeSubscription->customer);
        $this->assertEquals($membership->stripe_id, $stripeSubscription->items->data[0]->price->id);
        $this->assertEquals($subscription->starts_at->timestamp, $stripeSubscription->current_period_start);
        $this->assertEquals($subscription->starts_at->timestamp, $stripeSubscription->start_date);
        $this->assertEquals($subscription->cancels_at->timestamp, $stripeSubscription->current_period_end);
        $this->assertEquals($subscription->cancels_at->timestamp, $stripeSubscription->cancel_at);
        $this->assertEquals($subscription->canceled_at->timestamp, $stripeSubscription->canceled_at);
        $this->assertNull($stripeSubscription->ended_at);
        $this->assertEquals('active', $stripeSubscription->status);
    }

    public function test_a_member_can_subscribe_a_monthly_membership()
    {
        $member = $this->fakeMember();

        $this->actingAsMember($member);

        $membership = Membership::whereHas('product', function ($query) use ($member) {
            $query->where('market_id', $member->school->market_id);
        })
            ->whereHas('campaign', function ($query) {
                $query->whereNotNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where('is_recurring', true)
            ->get()
            ->random();

        $response = $this->postJson(route('api.v1.subscriptions.store'), [
            'membership_id' => $membership->id,
            'payment_token_id' => 'tok_mastercard',
        ]);

        $response->assertCreated()
            ->assertJsonSuccess();

        // Assert that the subscription was created in the database.
        $this->assertDatabaseCount('subscriptions', 1);
        $subscription = Subscription::first();
        $this->assertEquals($member->school->id, $subscription->school_id);
        $this->assertEquals($membership->id, $subscription->membership_id);
        $this->assertNotNull($subscription->stripe_id);
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertNotNull($subscription->starts_at);
        $this->assertNull($subscription->cancels_at);
        $this->assertNull($subscription->canceled_at);
        $this->assertEquals($subscription->starts_at, $subscription->current_period_starts_at);
        $this->assertEquals($subscription->starts_at->addMonth(), $subscription->current_period_ends_at);
        $this->assertNull($subscription->ended_at);
        $this->assertNull($subscription->custom_user_limit);

        // Assert that the subscription was created in Stripe.
        $marketId = $member->school->market->id;
        $stripeClient = new StripeClient(config("services.stripe.$marketId.secret"));
        $stripeSubscription = $stripeClient->subscriptions->retrieve($subscription->stripe_id);

        $this->assertEquals($subscription->stripe_id, $stripeSubscription->id);
        $this->assertEquals($member->school->stripe_id, $stripeSubscription->customer);
        $this->assertEquals($membership->stripe_id, $stripeSubscription->items->data[0]->price->id);
        $this->assertEquals($subscription->starts_at->timestamp, $stripeSubscription->current_period_start);
        $this->assertEquals($subscription->starts_at->timestamp, $stripeSubscription->start_date);
        $this->assertEquals($subscription->starts_at->addMonth()->timestamp, $stripeSubscription->current_period_end);
        $this->assertNull($stripeSubscription->cancel_at);
        $this->assertNull($stripeSubscription->canceled_at);
        $this->assertNull($stripeSubscription->ended_at);
        $this->assertEquals('active', $stripeSubscription->status);
    }
}
