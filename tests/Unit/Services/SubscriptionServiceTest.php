<?php

namespace Tests\Unit\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The subscription service instance.
     *
     * @var SubscriptionService
     */
    protected SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriptionService = new SubscriptionService();
    }

    /**
     * @see SubscriptionService::create()
     */
    public function test_it_creates_the_subscription(): void
    {
        $school = $this->fakeHomeschool();

        $attributes = [
            'school_id' => $school->id,
            'membership_id' => 1,
            'stripe_id' => 'sub_123',
            'starts_at' => fake()->dateTime(),
            'cancels_at' => fake()->dateTime(),
            'canceled_at' => fake()->dateTime(),
            'ended_at' => fake()->dateTime(),
            'status' => SubscriptionStatus::ACTIVE,
            'custom_user_limit' => fake()->numberBetween(1, 100)
        ];

        $subscription = $this->subscriptionService->create($attributes);

        // Assert the subscription was created in the database.
        $this->assertDatabaseCount('subscriptions', 1);

        // Assert the subscription has the correct values.
        $this->assertDatabaseHas('subscriptions', $attributes);

        // Assert it returns the subscription instance.
        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals($attributes['school_id'], $subscription->school_id);
        $this->assertEquals($attributes['membership_id'], $subscription->membership_id);
        $this->assertEquals($attributes['stripe_id'], $subscription->stripe_id);
        $this->assertEquals($attributes['starts_at'], $subscription->starts_at);
        $this->assertEquals($attributes['cancels_at'], $subscription->cancels_at);
        $this->assertEquals($attributes['canceled_at'], $subscription->canceled_at);
        $this->assertEquals($attributes['ended_at'], $subscription->ended_at);
        $this->assertEquals($attributes['status'], $subscription->status);
        $this->assertEquals($attributes['custom_user_limit'], $subscription->custom_user_limit);
    }

    /**
     * @see SubscriptionService::update()
     */
    public function test_it_updates_the_subscription(): void
    {
        // TODO
    }
}
