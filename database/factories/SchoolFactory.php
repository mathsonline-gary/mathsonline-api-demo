<?php

namespace Database\Factories;

use App\Models\Market;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'market_id' => fake()->numberBetween(1, Market::count()),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'fax' => fake()->e164PhoneNumber(),
            'address_line_1' => fake()->buildingNumber(),
            'address_line_2' => fake()->streetAddress(),
            'address_city' => fake()->city(),
            'address_state' => fake()->countryCode(),
            'address_postal_code' => fake()->postcode(),
            'address_country' => fake()->country(),
        ];
    }
}