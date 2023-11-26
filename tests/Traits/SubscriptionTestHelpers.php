<?php

namespace Tests\Traits;

use App\Enums\SubscriptionStatus;
use App\Models\Membership;
use App\Models\School;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

trait SubscriptionTestHelpers
{
    /**
     * Create a fake subscription for the given school and membership.
     *
     * @param School             $school
     * @param SubscriptionStatus $status
     * @param Membership|null    $membership
     * @param int                $count
     * @param array              $attributes
     *
     * @return Collection|Subscription
     */
    public function fakeSubscription(
        School             $school,
        SubscriptionStatus $status = SubscriptionStatus::ACTIVE,
        Membership         $membership = null,
        int                $count = 1,
        array              $attributes = []
    ): Collection|Subscription
    {
        $factory = Subscription::factory()
            ->ofSchool($school);

        if (!is_null($membership)) {
            $factory = $factory->withMembership($membership);
        }

        if ($status === SubscriptionStatus::CANCELED) {
            $factory = $factory->canceled();
        }

        if ($count > 1) {
            $factory = $factory->count($count);
        }

        return $factory->create($attributes);
    }

    /**
     * Assert that the given subscription has the expected attributes.
     *
     * @param array        $expected
     * @param Subscription $subscription
     *
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
