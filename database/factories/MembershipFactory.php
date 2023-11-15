<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Membership;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'price' => fake()->randomFloat(2, 0, 500),
            'period_in_months' => 12,
            'period_in_days' => null,
            'is_recurring' => fake()->boolean(),
            'user_limit' => fake()->numberBetween(0, 200),
            'stripe_id' => 'price_' . fake()->uuid,
            'note' => fake()->optional()->text(),
            'product_id' => Product::factory()->create()->id,
            'campaign_id' => Campaign::factory()->create()->id,
        ];
    }
}
