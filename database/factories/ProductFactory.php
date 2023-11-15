<?php

namespace Database\Factories;

use App\Enums\SchoolType;
use App\Models\Market;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'market_id' => Market::inRandomOrder()->first()->id,
            'name' => 'Fake Membership',
            'stripe_id' => 'prod_' . fake()->uuid,
            'school_type' => fake()->randomElement(array_column(SchoolType::cases(), 'value')),
        ];
    }
}
