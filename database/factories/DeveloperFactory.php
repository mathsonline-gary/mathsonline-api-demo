<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Developer>
 */
class DeveloperFactory extends Factory
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
                'type' => 'developer',
                'login' => fake()->unique()->userName(),
            ]);

        return [
            'user_id' => $user->id,
            'username' => $user->login,
            'email' => fake()->safeEmail(),
        ];
    }
}
