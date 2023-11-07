<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
            'canceled_at',
            'ended_at',
            'status',
            'custom_price',
            'custom_user_limit'
        ]);

        return Subscription::create($attributes);
    }

    /**
     * Search for subscriptions.
     *
     * @param array{
     *     school_id?: int,
     *     stripe_subscription_id?: string,
     * } $options
     * @return Collection<Subscription>
     */
    public function search(array $options): Collection
    {
        $query = Subscription::when(isset($options['school_id']), function ($query) use ($options) {
            $query->where('school_id', $options['school_id']);
        })
            ->when(isset($options['stripe_subscription_id']), function ($query) use ($options) {
                $query->where('stripe_subscription_id', $options['stripe_subscription_id']);
            });

        return $query->get();
    }

    /**
     * Cancel a subscription.
     *
     * @param Subscription $subscription
     * @param Carbon $canceled_at
     * @return void
     */
    public function cancel(Subscription $subscription, Carbon $canceled_at): void
    {
        $subscription->update([
            'canceled_at' => $canceled_at,
            'status' => SubscriptionStatus::CANCELED,
        ]);
    }
}
