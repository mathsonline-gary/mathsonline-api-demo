<?php

namespace Database\Factories;

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
            'stripe_subscription_id' => 'sub_' . $this->faker->uuid,
            'current_period_starts_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'current_period_ends_at' => now()->addDays($this->faker->numberBetween(1, 30)),
        ];
    }
}
