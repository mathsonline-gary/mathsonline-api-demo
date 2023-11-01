<?php

namespace Tests\Unit\Models;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @see Subscription::scopeActive()
     */
    public function test_it_gets_active_subscriptions(): void
    {
        $school = $this->fakeHomeschool(attributes: ['market_id' => 1]);

        $activeSubscriptions = $this->fakeSubscription($school, count: 2);
        $canceledSubscriptions = $this->fakeSubscription($school, status: SubscriptionStatus::CANCELED, count: 2);

        $this->assertCount(2, Subscription::active()->get());

        $activeSubscriptions->each(function (Subscription $subscription) {
            $this->assertTrue(Subscription::active()->get()->contains($subscription));
        });
    }
}
