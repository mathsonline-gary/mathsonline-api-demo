<?php

namespace Database\Factories\Users;

use App\Models\School;
use App\Models\Users\Student;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student()->create()->id,
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
        return $this->afterMaking(function (Student $student) {
            $student->credentials->update([
                'login' => $student->username,
            ]);
        })->afterCreating(function (Student $student) {
            $student->credentials->update([
                'login' => $student->username,
            ]);
        });
    }

    /**
     * Indicate the student to belong to a given school.
     *
     * @param School $school
     * @return StudentFactory
     */
    public function ofSchool(School $school): StudentFactory
    {
        return $this->state(function () use ($school) {
            return [
                'school_id' => $school->id,
            ];
        });
    }
}
