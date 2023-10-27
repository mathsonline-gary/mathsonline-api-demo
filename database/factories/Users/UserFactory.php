<?php

namespace Database\Factories\Users;

use App\Enums\UserType;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'login' => fake()->unique()->userName(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ];
    }

    /**
     * Indicate the user to be a student.
     *
     * @return UserFactory
     */
    public function student(): UserFactory
    {
        return $this->state(function () {
            return [
                'type' => UserType::TYPE_STUDENT->value,
            ];
        });
    }

    /**
     * Indicate the user to be a teacher.
     *
     * @return UserFactory
     */
    public function teacher(): UserFactory
    {
        return $this->state(function () {
            return [
                'type' => UserType::TYPE_TEACHER->value,
            ];
        });
    }

    /**
     * Indicate the user to be a member.
     *
     * @return UserFactory
     */
    public function member(): UserFactory
    {
        return $this->state(function () {
            return [
                'login' => fake()->safeEmail(),
                'type' => UserType::TYPE_MEMBER->value,
            ];
        });
    }

    /**
     * Indicate the user to be an admin.
     *
     * @return UserFactory
     */
    public function admin(): UserFactory
    {
        return $this->state(function () {
            return [
                'type' => UserType::TYPE_ADMIN->value,
            ];
        });
    }

    /**
     * Indicate the user to be a developer.
     *
     * @return UserFactory
     */
    public function developer(): UserFactory
    {
        return $this->state(function () {
            return [
                'type' => UserType::TYPE_DEVELOPER->value,
            ];
        });
    }
}
