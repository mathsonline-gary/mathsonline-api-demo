<?php

namespace Database\Factories\Users;

use App\Models\Market;
use App\Models\Users\Admin;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->admin()->create()->id,
            'market_id' => fake()->randomElement(Market::pluck('id')->all()),
            'username' => fake()->unique()->userName(),
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
        return $this->afterMaking(function (Admin $admin) {
            $admin->asUser()->update([
                'login' => $admin->username,
            ]);
        })->afterCreating(function (Admin $admin) {
            $admin->asUser()->update([
                'login' => $admin->username,
            ]);
        });
    }
}
