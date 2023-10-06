<?php

namespace Database\Factories;

use App\Enums\ActivityType;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(array_column(ActivityType::cases(), 'value')),
            'data' => [
                'key_1' => 'value_1',
                'key_2' => [
                    'key_2_1' => 'value_2_1',
                    'key_2_3' => 'value_2_3',
                ],
            ],
            'acted_at' => Carbon::now(),
        ];
    }
}
