<?php

namespace Tests\Traits;

use App\Models\School;
use App\Models\Users\Student;
use Illuminate\Database\Eloquent\Collection;

trait TestStudentHelpers
{
    /**
     * Create student(s) in the given school.
     *
     * @param School $school
     * @param int $count
     * @param array $attributes
     * @return Collection|Student
     */
    public function fakeStudent(School $school, int $count = 1, array $attributes = []): Collection|Student
    {
        $students = Student::factory()
            ->count($count)
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
        $this->actingAs($student, 'student');
    }
}
