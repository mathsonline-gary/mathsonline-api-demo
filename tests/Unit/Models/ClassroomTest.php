<?php

namespace Tests\Unit\Models;

use App\Models\Classroom;
use App\Models\ClassroomGroup;
use App\Models\School;
use App\Models\Users\Teacher;
use Database\Seeders\MarketSeeder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @see Classroom::school()
     */
    public function test_it_belongs_to_a_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($owner);

        $this->assertInstanceOf(BelongsTo::class, $classroom->school());
        $this->assertInstanceOf(School::class, $classroom->school);
        $this->assertEquals($school->id, $classroom->school->id);
    }

    /**
     * @see Classroom::owner()
     */
    public function test_it_belongs_to_an_owner(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($owner);

        $this->assertInstanceOf(BelongsTo::class, $classroom->owner());
        $this->assertInstanceOf(Teacher::class, $classroom->owner);
        $this->assertEquals($owner->id, $classroom->owner->id);
    }

    /**
     * @see Classroom::secondaryTeachers()
     */
    public function test_it_belongs_to_many_secondary_teachers(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($owner);

        $teachers = $this->fakeNonAdminTeacher($school, 5);

        $this->attachSecondaryTeachersToClassroom($classroom, $teachers->pluck('id')->toArray());

        $this->assertInstanceOf(BelongsToMany::class, $classroom->secondaryTeachers());
        $this->assertInstanceOf(Teacher::class, $classroom->secondaryTeachers()->getRelated());

        $this->assertCount($teachers->count(), $classroom->secondaryTeachers);
    }

    /**
     * @see Classroom::classroomGroups()
     */
    public function test_it_has_many_classroom_groups(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($owner);

        $customGroups = $this->fakeCustomClassroomGroup($classroom, 3);

        $this->assertInstanceOf(HasMany::class, $classroom->classroomGroups());
        $this->assertInstanceOf(ClassroomGroup::class, $classroom->classroomGroups()->getRelated());
        $this->assertCount($customGroups->count() + 1, $classroom->classroomGroups);
    }

    public function test_it_has_one_default_classroom_group()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($owner);

        $this->assertInstanceOf(HasOne::class, $classroom->defaultClassroomGroup());
        $this->assertInstanceOf(ClassroomGroup::class, $classroom->defaultClassroomGroup()->getRelated());
    }

    public function test_it_has_many_custom_classroom_groups()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($owner);
        $customClassroomGroups = $this->fakeCustomClassroomGroup($classroom, 5);

        $this->assertInstanceOf(HasMany::class, $classroom->customClassroomGroups());
        $this->assertInstanceOf(ClassroomGroup::class, $classroom->customClassroomGroups()->getRelated());
        $this->assertCount($customClassroomGroups->count(), $classroom->customClassroomGroups);
    }
}
