<?php

namespace Database\Factories\Users;

use App\Models\School;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $positions = [
            'Year 1 Teacher',
            'Year 2 Teacher',
            'Year 3 Teacher',
            'Year 4 Teacher',
            'Year 5 Teacher',
        ];

        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'title' => fake()->title(),
            'position' => fake()->randomElement($positions)
        ];
    }

    /**
     * Indicate the teacher has the administrator access.
     *
     * @return TeacherFactory
     */
    public function admin(): TeacherFactory
    {
        return $this->state(function () {
            return [
                'is_admin' => true,
            ];
        });
    }

    /**
     * Indicate the teacher has no administrator access.
     *
     * @return TeacherFactory
     */
    public function nonAdmin(): TeacherFactory
    {
        return $this->state(function () {
            return [
                'is_admin' => false,
            ];
        });
    }

    /**
     * Indicate the teacher to belong to a given school.
     *
     * @param School $school
     * @return TeacherFactory
     */
    public function ofSchool(School $school): TeacherFactory
    {
        return $this->state(function () use ($school) {
            return [
                'school_id' => $school->id,
            ];
        });
    }
}
