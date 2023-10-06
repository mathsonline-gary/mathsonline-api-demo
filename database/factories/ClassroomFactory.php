<?php

namespace Database\Factories;

use App\Enums\SchoolType;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Class ' . fake()->randomNumber(2),
        ];
    }

    /**
     * Indicate the classroom to belong to a given school.
     *
     * @param School $school
     * @return ClassroomFactory
     */
    public function ofSchool(School $school): ClassroomFactory
    {
        return $this->state(function () use ($school) {
            return [
                'school_id' => $school->id,
                'type_id' => match ($school->type) {
                    SchoolType::TRADITIONAL_SCHOOL => Classroom::TYPE_TRADITIONAL_CLASSROOM,
                    SchoolType::HOMESCHOOL => Classroom::TYPE_HOMESCHOOL_CLASSROOM,
                }
            ];
        });
    }


    /**
     * Indicate the classroom to owned by a given teacher.
     *
     * @param Teacher $teacher
     * @return ClassroomFactory
     */
    public function ownedBy(Teacher $teacher): ClassroomFactory
    {
        return $this->state(function () use ($teacher) {
            return [
                'owner_id' => $teacher->id,
            ];
        });
    }
}
