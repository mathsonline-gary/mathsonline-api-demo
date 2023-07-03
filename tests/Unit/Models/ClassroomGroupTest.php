<?php

namespace Tests\Unit\Models;

use App\Models\ClassroomGroup;
use App\Models\Users\Student;
use Database\Seeders\MarketSeeder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomGroupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @see ClassroomGroup::students()
     */
    public function test_it_has_many_students(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $teacher = $this->createAdminTeacher($school);
        $students = $this->createStudent($school, 5);
        $classroom = $this->createClassroom($teacher);
        $defaultClassroomGroup = $classroom->defaultClassroomGroup;
        $this->addStudentsToClassroomGroup($defaultClassroomGroup, $students->pluck('id')->toArray());

        $this->assertInstanceOf(BelongsToMany::class, $defaultClassroomGroup->students());
        $this->assertInstanceOf(Student::class, $defaultClassroomGroup->students()->getRelated());
        $this->assertCount($students->count(), $defaultClassroomGroup->students);
    }
}
