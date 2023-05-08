<?php

namespace Database\Factories;

use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
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
                'type' => 'admin',
                'login' => fake()->unique()->userName(),
            ]);

        return [
            'market_id' => fake()->numberBetween(1, Market::count()),
            'user_id' => $user->id,
            'username' => $user->login,
            'email' => fake()->safeEmail(),
        ];
    }
}
