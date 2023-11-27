<?php

namespace Database\Factories\Users;

use App\Models\Users\Developer;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Developer>
 */
class DeveloperFactory extends Factory
{
    protected $model = Developer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->developer()->create()->id,
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
        ];
    }

    /**
     * Configure the model factory: synchronize the username with the login identifier.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Developer $developer) {
            $user = $developer->asUser();

            $user->login = $developer->email;
            $user->email = $developer->email;
            $user->email_verified_at = now();

            $user->save();
        })->afterCreating(function (Developer $developer) {
            $user = $developer->asUser();

            $user->login = $developer->email;
            $user->email = $developer->email;
            $user->email_verified_at = now();

            $user->save();
        });
    }
}
