<?php

namespace Tests\Feature\Subscriptions;

use App\Models\Campaign;
use App\Models\Market;
use App\Models\Membership;
use App\Models\Product;
use Tests\TestCase;

class CreateSubscriptionTest extends TestCase
{
    protected string $routeName = 'api.v1.subscriptions.store';
    
    public function test_a_guest_is_unauthenticated_to_subscribe_a_membership()
    {
        $this->assertGuest();

        $response = $this->postJson(route($this->routeName), [
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

        $response = $this->postJson(route($this->routeName), [
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

        $response = $this->postJson(route($this->routeName), [
            'membership_id' => 1,
            'payment_token_id' => 'tok_mastercard',
        ]);

        $response->assertForbidden()
            ->assertJsonFragment([
                'message' => 'The member already has an active subscription.',
            ]);
    }

    public function test_a_member_cannot_subscribe_to_a_membership_for_another_market()
    {
        $member = $this->fakeMember();

        // Create a fake active membership for another market.
        $product = Product::factory()->create([
            'market_id' => Market::whereNot('id', $member->school->market_id)->inRandomOrder()->first()->id,
        ]);
        $campaign = Campaign::factory()->active()->create();
        $membership = Membership::factory()->create([
            'product_id' => $product->id,
            'campaign_id' => $campaign->id,
        ]);

        $this->actingAsMember($member);

        $response = $this->postJson(route($this->routeName), [
            'membership_id' => $membership->id,
            'payment_token_id' => 'tok_mastercard',
        ]);

        $response->assertUnprocessable()
            ->assertInvalid([
                'membership_id' => 'The selected membership is invalid. Please choose a different membership.',
            ]);
    }

    public function test_a_member_cannot_subscribe_to_an_expired_membership()
    {
        $member = $this->fakeMember();

        // Create a fake active membership for another market.
        $product = Product::factory()->create([
            'market_id' => $member->school->market_id,
        ]);
        $campaign = Campaign::factory()->expired()->create();
        $membership = Membership::factory()->create([
            'product_id' => $product->id,
            'campaign_id' => $campaign->id,
        ]);

        $this->actingAsMember($member);

        $response = $this->postJson(route($this->routeName), [
            'membership_id' => $membership->id,
            'payment_token_id' => 'tok_mastercard',
        ]);

        $response->assertUnprocessable()
            ->assertInvalid([
                'membership_id' => 'The selected membership is invalid. Please choose a different membership.',
            ]);
    }

    public function test_a_member_can_subscribe_a_twelve_month_membership()
    {
        $member = $this->fakeMember();

        $membership = Membership::whereHas('product', function ($query) use ($member) {
            $query->where('market_id', $member->school->market_id);
        })
            ->whereHas('campaign', function ($query) {
                $query->whereNotNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where('iterations', 1)
            ->where('period_in_months', 12)
            ->get()
            ->random();
        $this->actingAsMember($member);

        $response = $this->postJson(route($this->routeName), [
            'membership_id' => $membership->id,
            'payment_token_id' => 'tok_mastercard',
        ]);
        $response->assertCreated()
            ->assertJsonSuccessful();
    }

    public function test_a_member_can_subscribe_a_monthly_membership()
    {
        $member = $this->fakeMember();

        $membership = Membership::whereHas('product', function ($query) use ($member) {
            $query->where('market_id', $member->school->market_id);
        })
            ->whereHas('campaign', function ($query) {
                $query->whereNotNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where('iterations', null)
            ->get()
            ->random();

        $this->actingAsMember($member);

        $response = $this->postJson(route($this->routeName), [
            'membership_id' => $membership->id,
            'payment_token_id' => 'tok_mastercard',
        ]);

        $response->assertCreated()
            ->assertJsonSuccessful();
    }
}
