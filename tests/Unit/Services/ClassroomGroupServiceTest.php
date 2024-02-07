<?php

namespace Tests\Unit\Services;

use App\Exceptions\MaxClassroomGroupCountReachedException;
use App\Models\Classroom;
use App\Models\ClassroomGroup;
use App\Services\ClassroomGroupService;
use App\Services\ClassroomService;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @see ClassroomService
 */
class ClassroomGroupServiceTest extends TestCase
{
    use WithFaker;

    protected ClassroomGroupService $classroomGroupService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classroomGroupService = new ClassroomGroupService();
    }

    /**
     * @see ClassroomGroupService::createCustom()
     */
    public function test_it_adds_a_custom_classroom_group(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);
        $classroom = $this->fakeClassroom($teacher);

        // Assert that there is no custom group of the classroom.
        $this->assertEquals(0, $classroom->customClassroomGroups()->count());

        // Assert that it not hit the max limit of classroom groups.
        $this->assertLessThan(Classroom::MAX_CUSTOM_GROUP_COUNT, $classroom->classroomGroups()->count());

        $attributes = [
            'name' => 'Custom Group 1',
            'pass_grade' => 40,
            'attempts' => 4,
        ];

        // Add a custom classroom group.
        try {
            $customGroup = $this->classroomGroupService->createCustom($classroom, $attributes);

            // Assert that the custom group was created.
            $this->assertInstanceOf(ClassroomGroup::class, $customGroup);
            $this->assertEquals(1, $classroom->customClassroomGroups()->count());
            $this->assertEquals($customGroup->id, $classroom->customClassroomGroups()->first()->id);
            $this->assertEquals($classroom->id, $classroom->customClassroomGroups()->first()->classroom_id);
            $this->assertEquals($attributes['name'], $classroom->customClassroomGroups()->first()->name);
            $this->assertEquals($attributes['pass_grade'], $classroom->customClassroomGroups()->first()->pass_grade);
            $this->assertEquals($attributes['attempts'], $classroom->customClassroomGroups()->first()->attempts);
        } catch (MaxClassroomGroupCountReachedException) {
            $this->fail();
        }
    }

    /**
     * @see ClassroomGroupService::createCustom()
     */
    public function test_it_throw_an_exception_when_hits_the_max_limit_of_classroom_groups(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);
        $classroom = $this->fakeClassroom($teacher);
        $this->fakeCustomClassroomGroup($classroom, Classroom::MAX_CUSTOM_GROUP_COUNT);

        // Assert that the count of groups hit the max limit.
        $this->assertEquals(Classroom::MAX_CUSTOM_GROUP_COUNT, $classroom->customClassroomGroups()->count());

        // Expect that MaxClassroomGroupCountReachedException to be thrown.
        $this->expectException(MaxClassroomGroupCountReachedException::class);

        // Add a custom classroom group.
        $this->classroomGroupService->createCustom($classroom, [
            'name' => 'Custom Group 1',
            'pass_grade' => 40,
            'attempts' => 4,
        ]);
    }

    /**
     * @see ClassroomGroupService::update()
     */
    public function test_it_updates_a_classroom_group(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $defaultClassroomGroup = $classroom->defaultClassroomGroup;
        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom);

        $attributes = [
            'name' => fake()->name,
            'pass_grade' => fake()->numberBetween(0, 100),
            'attempts' => fake()->numberBetween(0, 10),
            'is_default' => fake()->boolean,    // This should be ignored.
        ];

        // Update the custom classroom group.
        $this->classroomGroupService->update($customClassroomGroup, $attributes);

        // Assert that the custom classroom group was updated correctly.
        $this->assertDatabaseHas('classroom_groups', [
            'id' => $customClassroomGroup->id,
            'classroom_id' => $classroom->id,
            'name' => $attributes['name'],
            'pass_grade' => $attributes['pass_grade'],
            'attempts' => $attributes['attempts'],
            'is_default' => false,
        ]);

        // Update the default classroom group.
        $this->classroomGroupService->update($defaultClassroomGroup, $attributes);

        // Assert that the default classroom group was updated correctly.
        $this->assertDatabaseHas('classroom_groups', [
            'id' => $defaultClassroomGroup->id,
            'classroom_id' => $classroom->id,
            'name' => $attributes['name'],
            'pass_grade' => $attributes['pass_grade'],
            'attempts' => $attributes['attempts'],
            'is_default' => true,
        ]);
    }

    /**
     * @see ClassroomGroupService::deleteCustom()
     */
    public function test_it_deletes_a_custom_classroom_group(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);
        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom);

        // Attach students to the custom classroom group.
        $students = $this->fakeStudent($school, 5);
        $this->attachStudentsToClassroomGroup($customClassroomGroup, $students->pluck('id')->toArray());

        $this->classroomGroupService->deleteCustom($customClassroomGroup);

        // Assert that the custom classroom group was deleted.
        $this->assertSoftDeleted('classroom_groups', ['id' => $customClassroomGroup->id]);

        // Assert that the associated students were moved to the default classroom group.
        $this->assertDatabaseCount('classroom_group_student', $students->count());
        $this->assertDatabaseMissing('classroom_group_student', ['classroom_group_id' => $customClassroomGroup->id]);
        foreach ($students as $student) {
            $this->assertDatabaseHas('classroom_group_student', [
                'classroom_group_id' => $classroom->defaultClassroomGroup->id,
                'student_id' => $student->id,
            ]);
        }
    }

    /**
     * @see ClassroomGroupService::assignStudents()
     */
    public function test_it_assigns_students_into_classroom_groups()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $student1 = $this->fakeStudent($school);
        $student2 = $this->fakeStudent($school);
        $student3 = $this->fakeStudent($school);

        $classroom = $this->fakeClassroom($adminTeacher);
        $customClassroomGroup1 = $this->fakeCustomClassroomGroup($classroom);

        // Assign $student1 and $student2 into $customClassroomGroup1.
        $this->classroomGroupService->assignStudents($customClassroomGroup1, [
            $student1->id,
            $student2->id,
        ]);

        // Assert that the students were assigned into the classroom group.
        $this->assertDatabaseCount('classroom_group_student', 2);
        $this->assertDatabaseHas('classroom_group_student', [
            'classroom_group_id' => $customClassroomGroup1->id,
            'student_id' => $student1->id,
        ])->assertDatabaseHas('classroom_group_student', [
            'classroom_group_id' => $customClassroomGroup1->id,
            'student_id' => $student2->id,
        ]);

        // Assign $student2 and $student3 into $customClassroomGroup1.
        $this->classroomGroupService->assignStudents($customClassroomGroup1, [
            $student2->id,
            $student3->id,
        ]);

        // Assert that the students were assigned into the classroom group without duplication.
        $this->assertDatabaseCount('classroom_group_student', 3);
        $this->assertDatabaseHas('classroom_group_student', [
            'classroom_group_id' => $customClassroomGroup1->id,
            'student_id' => $student1->id,
        ])->assertDatabaseHas('classroom_group_student', [
            'classroom_group_id' => $customClassroomGroup1->id,
            'student_id' => $student2->id,
        ])->assertDatabaseHas('classroom_group_student', [
            'classroom_group_id' => $customClassroomGroup1->id,
            'student_id' => $student3->id,
        ]);
    }
}
