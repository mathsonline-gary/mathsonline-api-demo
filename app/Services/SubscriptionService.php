<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
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

        return Subscription::create($attributes);
    }

    public function createWithStripeSubscription(int $schoolId, int $membershipId, StripeSubscription $stripeSubscription)
    {
        return $this->create([
            'school_id' => $schoolId,
            'membership_id' => $membershipId,
            'stripe_id' => $stripeSubscription->id,
            'starts_at' => $stripeSubscription->start_date,
            'cancels_at' => $stripeSubscription->cancel_at,
            'current_period_starts_at' => $stripeSubscription->current_period_start,
            'current_period_ends_at' => $stripeSubscription->current_period_end,
            'canceled_at' => $stripeSubscription->canceled_at,
            'ended_at' => $stripeSubscription->ended_at,
            'status' => $stripeSubscription->status,
        ]);
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
        $attributes = Arr::only($attributes, [
            'school_id',
            'membership_id',
            'stripe_id',
            'starts_at',
            'cancels_at',
            'canceled_at',
            'ended_at',
            'status',
            'custom_user_limit',
        ]);

        $subscription->update($attributes);

        return $subscription;
    }

    /**
     * Cancel a subscription.
     *
     * @param Subscription $subscription
     * @param Carbon|null $canceled_at
     * @return void
     */
    public function cancel(Subscription $subscription, Carbon $canceled_at = null): void
    {
        $subscription->update([
            'canceled_at' => $canceled_at ?? now(),
            'status' => SubscriptionStatus::CANCELED,
        ]);
    }
}
