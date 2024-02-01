<?php

namespace Database\Factories;

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
        $types = [
            Activity::TYPE_LOG_IN,
            Activity::TYPE_LOG_OUT,
            Activity::TYPE_CREATE_TEACHER,
            Activity::TYPE_UPDATE_TEACHER,
            Activity::TYPE_DELETE_TEACHER,
            Activity::TYPE_CREATE_STUDENT,
            Activity::TYPE_UPDATE_STUDENT,
            Activity::TYPE_DELETE_STUDENT,
        ];

        return [
            'type' => fake()->randomElement($types),
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
