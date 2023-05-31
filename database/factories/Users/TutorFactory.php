<?php

namespace Database\Factories\Users;

use App\Models\School;
use App\Models\Users\Tutor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tutor>
 */
class TutorFactory extends Factory
{
    protected $model = Tutor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type_id' => fake()->numberBetween(1, 3),
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ];
    }

    /**
     * Indicate the tutor is a primary tutor.
     *
     * @return TutorFactory
     */
    public function primary(): TutorFactory
    {
        return $this->state(function () {
            return [
                'type_id' => 1,
            ];
        });
    }

    /**
     * Indicate the tutor is a secondary tutor.
     *
     * @return TutorFactory
     */
    public function secondary(): TutorFactory
    {
        return $this->state(function () {
            return [
                'type_id' => 2,
            ];
        });
    }
}
