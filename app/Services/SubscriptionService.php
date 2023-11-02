<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Support\Arr;

class SubscriptionService
{
    /**
     * Create a new subscription.
     *
     * @param array $attributes
     * @return Subscription
     */
    public function create(array $attributes): Subscription
    {
        $attributes = Arr::only($attributes, [
            'school_id',
            'membership_id',
            'stripe_subscription_id',
            'starts_at',
            'cancels_at',
            'cancelled_at',
            'ended_at',
            'status',
            'custom_price',
            'custom_user_limit'
        ]);

        return Subscription::create($attributes);
    }
}
