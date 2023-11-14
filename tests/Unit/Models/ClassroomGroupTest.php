<?php

namespace Tests\Unit\Models;

use App\Models\ClassroomGroup;
use App\Models\Users\Student;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class ClassroomGroupTest extends TestCase
{
    /**
     * @see ClassroomGroup::students()
     */
    public function test_it_has_many_students(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);
        $students = $this->fakeStudent($school, 5);
        $classroom = $this->fakeClassroom($teacher);
        $defaultClassroomGroup = $classroom->defaultClassroomGroup;
        $this->attachStudentsToClassroomGroup($defaultClassroomGroup, $students->pluck('id')->toArray());

        $this->assertInstanceOf(BelongsToMany::class, $defaultClassroomGroup->students());
        $this->assertInstanceOf(Student::class, $defaultClassroomGroup->students()->getRelated());
        $this->assertCount($students->count(), $defaultClassroomGroup->students);
    }

    /**
     * @see ClassroomGroup::isDefault()
     */
    public function test_it_can_determine_if_it_is_the_default_classroom_group(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);
        $classroom = $this->fakeClassroom($teacher);
        $defaultClassroomGroup = $classroom->defaultClassroomGroup;
        $nonDefaultClassroomGroup = $this->fakeCustomClassroomGroup($classroom);

        $this->assertTrue($defaultClassroomGroup->isDefault());
        $this->assertFalse($nonDefaultClassroomGroup->isDefault());
    }
}
