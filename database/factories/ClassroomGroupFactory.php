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
     * Indicate that the classroom group belongs to the given classroom.
     *
     * @param Classroom $classroom
     * @return ClassroomGroupFactory
     */
    public function ofClassroom(Classroom $classroom): ClassroomGroupFactory
    {
        return $this->state(function () use ($classroom) {
            return [
                'classroom_id' => $classroom->id,
            ];
        });
    }

    /**
     * Indicate that the classroom group is the default group of the class.
     *
     * @return ClassroomGroupFactory
     */
    public function default(): ClassroomGroupFactory
    {
        return $this->state(function () {
            return [
                'is_default' => true,
            ];
        });
    }

    /**
     * Indicate that the classroom group is a custom group of the class.
     *
     * @return ClassroomGroupFactory
     */
    public function custom(): ClassroomGroupFactory
    {
        return $this->state(function () {
            return [
                'is_default' => false,
            ];
        });
    }
}
