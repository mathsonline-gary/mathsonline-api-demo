<?php

namespace Tests\Feature\Subscriptions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\Exception\ApiErrorException;
use Tests\TestCase;

class CreateSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_unauthenticated_to_subscribe_a_membership()
    {
        $this->assertGuest();

        $response = $this->postJson(route('api.v1.subscriptions.store'), [
            'membership_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_a_member_can_subscribe_a_fixed_period_membership()
    {
        try {
            $member = $this->fakeMember();
        } catch (ApiErrorException $e) {
            $this->fail($e->getMessage());
        }

        $this->actingAsMember($member);

        $response = $this->postJson(route('api.v1.subscriptions.store'), [
            'membership_id' => 1,
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['success' => true]);
    }
}
