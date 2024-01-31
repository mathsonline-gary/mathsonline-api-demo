<?php

namespace Tests\Unit\Services;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
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

    public function test_it_creates_the_subscription(): void
    {
        $school = $this->fakeHomeschool();

        $attributes = [
            'school_id' => $school->id,
            'membership_id' => 1,
            'stripe_id' => 'sub_123',
            'starts_at' => fake()->dateTime(),
            'cancels_at' => fake()->dateTime(),
            'current_period_starts_at' => fake()->dateTime(),
            'current_period_ends_at' => fake()->dateTime(),
            'canceled_at' => fake()->dateTime(),
            'ended_at' => fake()->dateTime(),
            'status' => Subscription::STATUS_ACTIVE,
            'custom_user_limit' => fake()->numberBetween(1, 100)
        ];

        $subscription = $this->subscriptionService->create($attributes);

        // Assert the subscription was created in the database.
        $this->assertDatabaseCount('subscriptions', 1);

        // Assert the subscription has the correct values.
        $this->assertDatabaseHas('subscriptions', $attributes);

        // Assert it returns the subscription instance.
        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertSubscriptionAttributes($attributes, $subscription);
    }

    public function test_it_updates_the_subscription(): void
    {
        $school = $this->fakeHomeschool(1, ['market_id' => 1]);

        $subscription = $this->fakeSubscription($school);

        $attributes = [
            'membership_id' => 1,
            'starts_at' => fake()->dateTime(),
            'cancels_at' => fake()->dateTime(),
            'current_period_starts_at' => fake()->dateTime(),
            'current_period_ends_at' => fake()->dateTime(),
            'canceled_at' => fake()->dateTime(),
            'ended_at' => fake()->dateTime(),
            'status' => Subscription::STATUS_INCOMPLETE,
            'custom_user_limit' => fake()->numberBetween(1, 100)
        ];

        $subscription = $this->subscriptionService->update($subscription, $attributes);

        // Assert that no new subscriptions were created.
        $this->assertDatabaseCount('subscriptions', 1);

        // Assert the subscription has the correct values.
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'school_id' => $school->id,
            ...$attributes
        ]);
    }

    public function test_it_does_not_update_school_id()
    {
        $school = $this->fakeHomeschool(1, ['market_id' => 1]);

        $subscription = $this->fakeSubscription($school);

        $attributes = [
            'school_id' => $school->id + 1,
            'membership_id' => 1,
            'starts_at' => fake()->dateTime(),
            'cancels_at' => fake()->dateTime(),
            'current_period_starts_at' => fake()->dateTime(),
            'current_period_ends_at' => fake()->dateTime(),
            'canceled_at' => fake()->dateTime(),
            'ended_at' => fake()->dateTime(),
            'status' => Subscription::STATUS_INCOMPLETE,
            'custom_user_limit' => fake()->numberBetween(1, 100)
        ];

        $subscription = $this->subscriptionService->update($subscription, $attributes);

        // Assert that no new subscriptions were created.
        $this->assertDatabaseCount('subscriptions', 1);

        // Assert that 'school_id' was not updated.
        $this->assertDatabaseMissing('subscriptions', [
            'id' => $subscription->id,
            'school_id' => $attributes['school_id'],
        ]);
    }

    public function test_it_does_not_update_stripe_id()
    {
        $school = $this->fakeHomeschool(1, ['market_id' => 1]);

        $subscription = $this->fakeSubscription($school);

        $attributes = [
            'stripe_id' => 'sub_' . fake()->text(),
            'membership_id' => 1,
            'starts_at' => fake()->dateTime(),
            'cancels_at' => fake()->dateTime(),
            'current_period_starts_at' => fake()->dateTime(),
            'current_period_ends_at' => fake()->dateTime(),
            'canceled_at' => fake()->dateTime(),
            'ended_at' => fake()->dateTime(),
            'status' => Subscription::STATUS_INCOMPLETE,
            'custom_user_limit' => fake()->numberBetween(1, 100)
        ];

        $subscription = $this->subscriptionService->update($subscription, $attributes);

        // Assert that no new subscriptions were created.
        $this->assertDatabaseCount('subscriptions', 1);

        // Assert that 'stripe_id' was not updated.
        $this->assertDatabaseMissing('subscriptions', [
            'id' => $subscription->id,
            'stripe_id' => $attributes['stripe_id'],
        ]);
    }
}
