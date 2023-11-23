<?php

namespace Tests\Traits;

use App\Enums\SubscriptionStatus;
use App\Models\Membership;
use App\Models\School;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait SubscriptionTestHelpers
{
    /**
     * Create a fake subscription for the given school and membership.
     *
     * @param School $school
     * @param SubscriptionStatus $status
     * @param Membership|null $membership
     * @param int $count
     * @return Collection|Subscription
     */
    public function fakeSubscription(School $school, SubscriptionStatus $status = SubscriptionStatus::ACTIVE, Membership $membership = null, int $count = 1): Collection|Subscription
    {
        // If no membership is provided, get a random active one from the school's market.
        if (!$membership) {
            $membership = Membership::whereHas('product', function (Builder $query) use ($school) {
                $query->where('market_id', $school->market_id);
            })
                ->whereHas('campaign', function (Builder $query) {
                    $query->whereNotNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->get()
                ->random();
        }

        $factory = Subscription::factory();

        $attributes['status'] = $status;
        $attributes['school_id'] = $school->id;
        $attributes['membership_id'] = $membership->id;

        // Set dates based on status
        switch ($status) {
            case SubscriptionStatus::ACTIVE:
                $attributes['starts_at'] = now()->subDays(fake()->numberBetween(1, 30));
                $attributes['canceled_at'] = null;
                $attributes['ended_at'] = null;
                break;

            default:
                $attributes['starts_at'] = now()->subDays(fake()->numberBetween(30, 100));
                $attributes['canceled_at'] = now()->subDays(fake()->numberBetween(1, 30));
                $attributes['ended_at'] = $attributes['canceled_at'];
                break;
        }

        // Set 'cancels_at' based on membership.
        if ($membership->is_recurring) {
            $attributes['cancels_at'] = null;
        } elseif ($membership->period_in_months) {
            $attributes['cancels_at'] = $attributes['starts_at']->addMonths($membership->period_in_months);
        } else {
            $attributes['cancels_at'] = $attributes['starts_at']->addDays($membership->period_in_days);
        }

        if ($count > 1) {
            $factory = $factory->count($count);
        }

        return $factory->create($attributes);
    }

    /**
     * Assert that the given subscription has the expected attributes.
     *
     * @param array $expected
     * @param Subscription $subscription
     * @return void
     */
    public function assertSubscriptionAttributes(array $expected, Subscription $subscription): void
    {
        foreach ($expected as $attribute => $value) {
            switch ($attribute) {
                case 'status':
                    $value instanceof SubscriptionStatus
                        ? $this->assertEquals(
                        $value,
                        $subscription->status,
                        'The subscription attribute status does not match the expected value.'
                    )
                        : $this->assertEquals(
                        $value,
                        $subscription->status->value,
                        'The subscription attribute status does not match the expected value.'
                    );

                    break;

                case 'starts_at':
                case 'cancels_at':
                case 'current_period_starts_at':
                case 'current_period_ends_at':
                case 'canceled_at':
                case 'ended_at':
                    if (is_null($value)) {
                        $this->assertNull($subscription->{$attribute}, "The subscription attribute '$attribute' expected to be null.");
                    } else {
                        $value instanceof Carbon
                            ? $this->assertEquals(
                            $value,
                            $subscription->{$attribute},
                            "The subscription attribute '$attribute' does not match the expected value."
                        )
                            : $this->assertEquals(
                            new Carbon($value),
                            $subscription->{$attribute},
                            "The subscription attribute '$attribute' does not match the expected value."
                        );
                    }

                    break;

                default:
                    $this->assertEquals(
                        $value,
                        $subscription->{$attribute},
                        "The subscription attribute '$attribute' does not match the expected value."
                    );

                    break;
            }
        }
    }
}
