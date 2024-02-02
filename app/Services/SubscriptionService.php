<?php

namespace App\Services;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Stripe\Subscription as StripeSubscription;

class SubscriptionService
{
    /**
     * Create a new subscription.
     *
     * @param array $attributes
     *
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
     *     pagination?: bool,
     *     with_membership?: bool,
     * } $options
     *
     * @return Collection<Subscription>
     */
    public function search(array $options): Collection
    {
        $options = Arr::only($options, [
            'school_id',
            'stripe_id',
            'pagination',
            'with_membership',
        ]);

        $query = Subscription::when(isset($options['school_id']), function ($query) use ($options) {
            $query->where('school_id', $options['school_id']);
        })
            ->when(isset($options['stripe_id']), function ($query) use ($options) {
                $query->where('stripe_id', $options['stripe_id']);
            })
            ->when(isset($options['with_membership']), function ($query) {
                $query->with('membership');
            });

        return $options['pagination'] ?? true
            ? $query->paginate($options['per_page'] ?? 20)->withQueryString()
            : $query->get();
    }

    /**
     * Update a subscription.
     *
     * @param Subscription $subscription
     * @param array        $attributes
     *
     * @return Subscription
     */
    public function update(Subscription $subscription, array $attributes): Subscription
    {
        $attributes = Arr::only($attributes, $subscription->getFillable());

        $subscription->update($attributes);

        return $subscription;
    }

    /**
     * Parse the subscription attributes from the given Stripe subscription.
     *
     * @param StripeSubscription $stripeSubscription
     *
     * @return array
     */
    public function parseAttributesFromStripeSubscription(StripeSubscription $stripeSubscription): array
    {
        return [
            'stripe_id' => $stripeSubscription->id,
            'starts_at' => new Carbon($stripeSubscription->start_date),
            'cancels_at' => $stripeSubscription->cancel_at ? new Carbon($stripeSubscription->cancel_at) : null,
            'current_period_starts_at' => new Carbon($stripeSubscription->current_period_start),
            'current_period_ends_at' => new Carbon($stripeSubscription->current_period_end),
            'canceled_at' => $stripeSubscription->canceled_at ? new Carbon($stripeSubscription->canceled_at) : null,
            'ended_at' => $stripeSubscription->ended_at ? new Carbon($stripeSubscription->ended_at) : null,
            'status' => $stripeSubscription->status,
        ];
    }

}
