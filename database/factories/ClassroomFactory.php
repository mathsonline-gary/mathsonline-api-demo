<?php

namespace Database\Factories;

use App\Enums\EnumClassroomType;
use App\Enums\EnumSchoolType;
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
            'pass_grade' => fake()->numberBetween(0, 90),
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
                'type' => match ($school->type) {
                    EnumSchoolType::TraditionalSchool => EnumClassroomType::TraditionalClassroom,
                    EnumSchoolType::Homeschool => EnumClassroomType::HomeschoolClassroom,
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
