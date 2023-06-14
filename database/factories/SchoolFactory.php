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
            'market_id' => fake()->randomElement(Market::pluck('id')->all()),
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
            'type' => fake()->randomElement([
                School::HOMESCHOOL,
                School::TRADITIONAL_SCHOOL
            ]),
        ];
    }

    /**
     * Indicate that the school type is homeschool.
     *
     * @return SchoolFactory
     */
    public function homeschool(): SchoolFactory
    {
        return $this->state(function (array $attribute) {
            return [
                'type' => School::HOMESCHOOL,
            ];
        });
    }

    /**
     * Indicate that the school type is traditional school.
     *
     * @return SchoolFactory
     */
    public function traditionalSchool(): SchoolFactory
    {
        return $this->state(function (array $attribute) {
            return [
                'type' => School::TRADITIONAL_SCHOOL,
            ];
        });
    }
}
