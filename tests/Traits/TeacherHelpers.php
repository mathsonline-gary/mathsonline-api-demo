<?php

namespace Tests\Traits;

use App\Models\School;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Collection;

trait TeacherHelpers
{

    /**
     * Create teacher(s) with admin access.
     *
     * @param School $school
     * @param int $count
     * @param array $attributes
     * @return Collection<Teacher>|Teacher
     */
    public function createTeacherAdmin(School $school, int $count = 1, array $attributes = []): Collection|Teacher
    {
        $teachers = Teacher::factory()
            ->count($count)
            ->admin()
            ->create([
                ...$attributes,
                'school_id' => $school->id,
            ]);

        return $count === 1 ? $teachers->first() : $teachers;
    }

    /**
     * Create non-admin teacher(s) in a given school.
     *
     * @param School $school
     * @param int $count
     * @param array $attributes
     * @return Collection<Teacher>|Teacher
     */
    public function createNonAdminTeacher(School $school, int $count = 1, array $attributes = []): Collection|Teacher
    {
        $teachers = Teacher::factory()
            ->count($count)
            ->nonAdmin()
            ->create([
                ...$attributes,
                'school_id' => $school->id,
            ]);

        return $count === 1 ? $teachers->first() : $teachers;
    }

    /**
     * Set the currently logged-in teacher for the application.
     *
     * @param Teacher $teacher
     * @return void
     */
    public function actingAsTeacher(Teacher $teacher): void
    {
        $this->actingAs($teacher, 'teacher');
    }
}
