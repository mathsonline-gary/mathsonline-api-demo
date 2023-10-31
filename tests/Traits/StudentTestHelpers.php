<?php

namespace Tests\Traits;

use App\Models\School;
use App\Models\Users\Student;
use App\Models\Users\StudentSetting;
use Illuminate\Database\Eloquent\Collection;

trait StudentTestHelpers
{
    /**
     * Create student(s) in the given school.
     *
     * @param School|null $school
     * @param int $count
     * @param array $attributes
     * @return Collection|Student
     */
    public function fakeStudent(School $school = null, int $count = 1, array $attributes = []): Collection|Student
    {
        $school ??= $this->fakeSchool();

        $students = Student::factory()
            ->count($count)
            ->has(
                StudentSetting::factory()
                    ->count(1),
                'settings'
            )
            ->create([
                ...$attributes,
                'school_id' => $school->id,
            ]);

        return $count === 1 ? $students->first() : $students;
    }

    /**
     * Set the currently logged-in student for the application.
     *
     * @param Student $student
     * @return void
     */
    public function actingAsStudent(Student $student): void
    {
        $this->actingAs($student->asUser());
    }
}
