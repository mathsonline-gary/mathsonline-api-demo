<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Membership;
use App\Models\Product;
use App\Models\School;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stripe_id' => 'sub_' . $this->faker->uuid,
            'starts_at' => now()->subMonths($this->faker->numberBetween(1, 30)),
            'cancels_at' => now()->addMonths($this->faker->numberBetween(1, 30)),
            'current_period_starts_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'current_period_ends_at' => now()->addDays($this->faker->numberBetween(1, 30)),
            'canceled_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'ended_at' => null,
            'status' => SubscriptionStatus::ACTIVE,
            'custom_user_limit' => null,
        ];
    }

    /**
     * Indicate the school of the subscription.
     *
     * @param School $school
     *
     * @return SubscriptionFactory
     */
    public function ofSchool(School $school): SubscriptionFactory
    {
        return $this->state(function () use ($school) {
            $attributes = [
                'school_id' => $school->id,
            ];

            $product = Product::where([
                'market_id' => $school->market_id,
                'school_type' => $school->type,
            ])
                ->inRandomOrder()
                ->first();

            if ($product) {
                $membership = Membership::where('product_id', $product->id)
                    ->inRandomOrder()
                    ->first();

                $attributes['membership_id'] = $membership
                    ? $membership->id
                    : Membership::factory()->create([
                        'product_id' => $product->id,
                    ])->id;
            } else {
                $product = Product::factory()->create([
                    'market_id' => $school->market_id,
                    'school_type' => $school->type,
                ]);

                $attributes['membership_id'] = Membership::factory()->create([
                    'product_id' => $product->id,
                ])->id;
            }

            return $attributes;
        });
    }

    /**
     * Indicate the membership of the subscription.
     *
     * @param Membership $membership
     *
     * @return SubscriptionFactory
     */
    public function withMembership(Membership $membership): SubscriptionFactory
    {
        return $this->state(function () use ($membership) {
            return [
                'membership_id' => $membership->id,
            ];
        });
    }

    /**
     * Indicate the subscription is canceled.
     *
     * @return SubscriptionFactory
     */
    public function canceled(): SubscriptionFactory
    {
        return $this->state(function () {
            return [
                'canceled_at' => now()->subDays($this->faker->numberBetween(10, 30)),
                'ended_at' => now()->subDays($this->faker->numberBetween(1, 10)),
                'status' => SubscriptionStatus::CANCELED,
            ];
        });
    }

}
