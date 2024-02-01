<?php

namespace Tests\Unit\Models;

use App\Models\Membership;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    /**
     * @see Subscription::scopeActive()
     */
    public function test_it_gets_active_subscriptions(): void
    {
        $school = $this->fakeHomeschool(attributes: ['market_id' => 1]);

        $activeSubscriptions = $this->fakeSubscription($school, count: 2);
        $canceledSubscriptions = $this->fakeSubscription($school, status: Subscription::STATUS_CANCELED, count: 2);

        $this->assertCount(2, Subscription::active()->get());

        $activeSubscriptions->each(function (Subscription $subscription) {
            $this->assertTrue(Subscription::active()->get()->contains($subscription));
        });

        $canceledSubscriptions->each(function (Subscription $subscription) {
            $this->assertFalse(Subscription::active()->get()->contains($subscription));
        });
    }

    public function test_it_gets_associated_membership(): void
    {
        $school = $this->fakeHomeschool(attributes: ['market_id' => 1]);

        $subscription = $this->fakeSubscription($school);

        $this->assertInstanceOf(BelongsTo::class, $subscription->membership());
        $this->assertInstanceOf(Membership::class, $subscription->membership);
        $this->assertEquals($subscription->membership_id, $subscription->membership->id);
    }
}
