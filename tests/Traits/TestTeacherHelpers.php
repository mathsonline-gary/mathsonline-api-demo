<?php

namespace Tests\Traits;

use App\Models\School;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Collection;

trait TestTeacherHelpers
{
    public function fakeTeacher(School $school = null, int $count = 1, array $attributes = []): Collection|Teacher
    {
        $school ??= $this->fakeTraditionalSchool();

        $teachers = Teacher::factory()
            ->count($count)
            ->create([
                ...$attributes,
                'school_id' => $school->id,
            ]);

        return $count === 1 ? $teachers->first() : $teachers;
    }

    /**
     * Create fake teacher(s) with admin access.
     *
     * @param School|null $school
     * @param int $count
     * @param array $attributes
     * @return Collection<Teacher>|Teacher
     */
    public function fakeAdminTeacher(School $school = null, int $count = 1, array $attributes = []): Collection|Teacher
    {
        $school ??= $this->fakeTraditionalSchool();

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
     * @param School|null $school
     * @param int $count
     * @param array $attributes
     * @return Collection<Teacher>|Teacher
     */
    public function fakeNonAdminTeacher(School $school = null, int $count = 1, array $attributes = []): Collection|Teacher
    {
        $school ??= $this->fakeTraditionalSchool();

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
        $this->actingAs($teacher->asUser());
    }
}
