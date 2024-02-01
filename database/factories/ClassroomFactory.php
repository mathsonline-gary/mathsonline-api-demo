<?php

namespace Database\Factories;

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
            'mastery_enabled' => fake()->boolean,
            'self_rating_enabled' => fake()->boolean,
        ];
    }

    /**
     * Indicate the classroom to belong to a given school.
     *
     * @param School $school
     *
     * @return ClassroomFactory
     */
    public function ofSchool(School $school): ClassroomFactory
    {
        return $this->state(function () use ($school) {
            return [
                'school_id' => $school->id,
                'type' => match ($school->type) {
                    School::TYPE_TRADITIONAL_SCHOOL => Classroom::TYPE_TRADITIONAL_CLASSROOM,
                    School::TYPE_HOMESCHOOL => Classroom::TYPE_HOMESCHOOL_CLASSROOM,
                },
                'year_id' => $school->market->years->random()->id,
            ];
        });
    }


    /**
     * Indicate the classroom to owned by a given teacher.
     *
     * @param Teacher $teacher
     *
     * @return ClassroomFactory
     */
    public function ownedBy(Teacher $teacher): ClassroomFactory
    {
        return $this->for($teacher, 'owner');
    }
}
