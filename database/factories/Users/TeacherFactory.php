<?php

namespace Database\Factories\Users;

use App\Models\School;
use App\Models\Users\Teacher;
use App\Models\Users\User;
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
            'user_id' => User::factory()->teacher()->create()->id,
            'username' => fake()->unique()->userName(),
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'title' => fake()->title(),
            'position' => fake()->randomElement($positions),
            'is_admin' => fake()->boolean(),
        ];
    }

    /**
     * Configure the model factory: synchronize the username with the login identifier.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Teacher $teacher) {
            $teacher->asUser()->update([
                'login' => $teacher->username,
            ]);
        })->afterCreating(function (Teacher $teacher) {
            $teacher->asUser()->update([
                'login' => $teacher->username,
            ]);
        });
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
