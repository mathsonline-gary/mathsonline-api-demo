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
            'balloon_tips_enabled' => fake()->boolean,
            'results_enabled' => fake()->boolean,
            'confetti_enabled' => fake()->boolean,
            'colour' => fake()->numberBetween(0, 255),
            'closed_captions_language' => fake()->randomElement(['en', 'es']),
        ];
    }
}
