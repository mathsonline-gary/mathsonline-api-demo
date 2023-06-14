<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\ClassroomGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassroomGroup>
 */
class ClassroomGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Group ' . fake()->randomNumber(),
            'pass_grade' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that whether the classroom group is the default group for the class.
     *
     * @param bool $isDefault
     * @return ClassroomGroupFactory
     */
    public function default(bool $isDefault): ClassroomGroupFactory
    {
        return $this->state(function () use ($isDefault) {
            return [
                'is_default' => $isDefault,
            ];
        });
    }
}
