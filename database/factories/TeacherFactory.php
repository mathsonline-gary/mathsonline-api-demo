<?php

namespace Database\Factories;

use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
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
                'type' => 'teacher',
                'login' => fake()->unique()->userName(),
            ]);

        $positions = [
            'Year 1 Teacher',
            'Year 2 Teacher',
            'Year 3 Teacher',
            'Year 4 Teacher',
            'Year 5 Teacher',
        ];

        return [
            'user_id' => $user->id,
            'username' => $user->login,
            'email' => fake()->safeEmail(),
            'title' => fake()->title(),
            'position' => fake()->randomElement($positions)
        ];
    }
}
