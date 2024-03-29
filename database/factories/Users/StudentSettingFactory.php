<?php

namespace Database\Factories\Users;

use App\Models\Users\StudentSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentSetting>
 */
class StudentSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expired_tasks_excluded' => fake()->boolean,
            'balloon_tips_enabled' => fake()->boolean,
            'results_enabled' => fake()->boolean,
            'confetti_enabled' => fake()->boolean,
            'closed_captions_language' => fake()->randomElement(['en', 'es']),
        ];
    }
}
