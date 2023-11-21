<?php

namespace App\Services;

use App\Models\Subscription;
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
            'stripe_id',
            'starts_at',
            'cancels_at',
            'current_period_starts_at',
            'current_period_ends_at',
            'canceled_at',
            'ended_at',
            'status',
            'custom_user_limit'
        ]);

        $subscription = new Subscription($attributes);

        $subscription->school_id = $attributes['school_id'];
        $subscription->stripe_id = $attributes['stripe_id'];

        $subscription->save();

        return $subscription;
    }

    /**
     * Search for subscriptions.
     *
     * @param array{
     *     school_id?: int,
     *     stripe_id?: string,
     * } $options
     * @return Collection<Subscription>
     */
    public function search(array $options): Collection
    {
        $options = Arr::only($options, [
            'school_id',
            'stripe_id',
        ]);

        $query = Subscription::when(isset($options['school_id']), function ($query) use ($options) {
            $query->where('school_id', $options['school_id']);
        })
            ->when(isset($options['stripe_id']), function ($query) use ($options) {
                $query->where('stripe_id', $options['stripe_id']);
            });

        return $query->get();
    }

    /**
     * Update a subscription.
     *
     * @param Subscription $subscription
     * @param array $attributes
     * @return Subscription
     */
    public function update(Subscription $subscription, array $attributes): Subscription
    {
        $attributes = Arr::only($attributes, $subscription->getFillable());

        $subscription->update($attributes);

        return $subscription;
    }

    /**
     * Find a subscription by its Stripe ID.
     *
     * @param string $stripeId
     * @return Subscription|null
     */
    public function findByStripeId(string $stripeId): ?Subscription
    {
        return Subscription::where('stripe_id', $stripeId)->first();
    }
}
