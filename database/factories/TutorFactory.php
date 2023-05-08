<?php

namespace Database\Factories;

use App\Models\Market;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tutor>
 */
class TutorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()
            ->unverified()
            ->create([
                'type' => 'tutor',
                'login' => fake()->safeEmail(),
            ]);

        return [
            'user_id' => $user->id,
            'type_id' => fake()->numberBetween(1, 3),
            'email' => $user->login,
        ];
    }
}
