<?php

namespace Database\Factories;

use App\Enums\ActionTypes;
use App\Models\Action;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Action>
 */
class ActionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'action' => fake()->randomElement(array_column(ActionTypes::cases(), 'value')),
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
