<?php

namespace Tests\Unit\Services;

use App\Models\Classroom;
use App\Services\ClassroomService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * @see ClassroomService
 */
class ClassroomServiceTest extends TestCase
{
    use WithFaker;

    protected ClassroomService $classroomService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classroomService = new ClassroomService();
    }

    /**
     * @see ClassroomService::search()
     */
    public function test_it_searches_classrooms_by_school_id(): void
    {
        // Create classrooms in school 1.
        $school1 = $this->fakeTraditionalSchool();
        $teacher1 = $this->fakeAdminTeacher($school1);
        $this->fakeClassroom($teacher1, 5);

        // Create classrooms in school 2.
        $school2 = $this->fakeTraditionalSchool();
        $teacher2 = $this->fakeAdminTeacher($school2);
        $this->fakeClassroom($teacher2, 5);

        $result = $this->classroomService->search(['school_id' => $school1->id]);

        // Assert that it returns a pagination by default.
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);

        // Assert that it returns the correct number of classrooms.
        $this->assertCount(5, $result->items());

        // Assert that all classrooms belong to $school1.
        $this->assertTrue($result->every(function ($classroom) use ($school1) {
            return $classroom->school_id === $school1->id;
        }));

        // Assert that all classrooms don't belong to school2.
        $this->assertFalse($result->contains(function ($classroom) use ($school2) {
            return $classroom->school_id === $school2->id;
        }));
    }

    /**
     * @see ClassroomService::search()
     */
    public function test_it_fuzzy_searches_classrooms(): void
    {
        $school = $this->fakeTraditionalSchool();
        $owner = $this->fakeAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($owner, 1, ['name' => 'Class 1']);
        $classroom2 = $this->fakeClassroom($owner, 1, ['name' => 'Classroom 2']);
        $classroom3 = $this->fakeClassroom($owner, 1, ['name' => 'Class 3']);

        $result1 = $this->classroomService->search(['search_key' => '1']);
        $result2 = $this->classroomService->search(['search_key' => 'room']);
        $result3 = $this->classroomService->search(['search_key' => 'class']);

        // Assert that $result1 is correct.
        $this->assertTrue($result1->contains($classroom1));
        $this->assertFalse($result1->contains($classroom2));
        $this->assertFalse($result1->contains($classroom3));

        // Assert that $result2 is correct.
        $this->assertFalse($result2->contains($classroom1));
        $this->assertTrue($result2->contains($classroom2));
        $this->assertFalse($result2->contains($classroom3));

        // Assert that $result3 is correct.
        $this->assertTrue($result3->contains($classroom1));
        $this->assertTrue($result3->contains($classroom2));
        $this->assertTrue($result3->contains($classroom3));
    }

    /**
     * @see ClassroomService::search()
     */
    public function test_it_returns_search_result_without_pagination(): void
    {
        $school = $this->fakeTraditionalSchool();
        $owner = $this->fakeAdminTeacher($school);
        $classrooms = $this->fakeClassroom($owner, 30);

        $result = $this->classroomService->search(['pagination' => false]);

        // Assert that the result is a collection of classrooms instead of pagination.
        $this->assertInstanceOf(Collection::class, $result);

        // Assert that the result contains all searched data, rather than paginating it.
        $this->assertCount($classrooms->count(), $result);
    }

    /**
     * @see ClassroomService::find()
     */
    public function test_it_finds_a_classroom_with_relationships()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $teachers = $this->fakeNonAdminTeacher($school, 2);
        $classroom = $this->fakeClassroom($adminTeacher);
        $this->fakeCustomClassroomGroup($classroom, 2);
        $this->attachSecondaryTeachersToClassroom($classroom, $teachers->pluck('id')->toArray());

        // Call find() method with default options.
        $result = $this->classroomService->find($classroom->id, [
            'with_school' => true,
            'with_owner' => true,
            'with_secondary_teachers' => true,
            'with_groups' => true,
        ]);

        // Assert that the classroom was found.
        $this->assertInstanceOf(Classroom::class, $result);

        // Assert that the result is correct.
        $this->assertEquals($classroom->id, $result->id);

        // Assert that the loaded relationships are correct.
        $this->assertTrue($result->relationLoaded('school'));
        $this->assertTrue($result->relationLoaded('owner'));
        $this->assertTrue($result->relationLoaded('secondaryTeachers'));
        $this->assertTrue($result->relationLoaded('classroomGroups'));
    }

    /**
     * @see ClassroomService::create()
     */
    public function test_it_creates_a_classroom()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);
        $secondaryTeachers = $this->fakeNonAdminTeacher($school, 2);

        $attributes = [
            'school_id' => $school->id,
            'owner_id' => $teacher->id,
            'year_id' => 1,
            'type' => Classroom::TYPE_TRADITIONAL_CLASSROOM,
            'name' => 'Test Class',
            'pass_grade' => 80,
            'attempts' => 1,
            'secondary_teacher_ids' => $secondaryTeachers->pluck('id')->toArray(),
            "groups" => [
                [
                    "name" => "Test Class Group 1",
                    "pass_grade" => 90,
                    "attempts" => 1
                ],
                [
                    "name" => "Test Class Group 2",
                    "pass_grade" => 60,
                    "attempts" => 3
                ],
            ],
        ];

        $classroom = $this->classroomService->create($attributes);

        // Assert that the classroom was created correctly.
        $this->assertInstanceOf(Classroom::class, $classroom);
        $this->assertEquals($attributes['school_id'], $classroom->school_id);
        $this->assertEquals($attributes['owner_id'], $classroom->owner_id);
        $this->assertEquals($attributes['type'], $classroom->type);
        $this->assertEquals($attributes['name'], $classroom->name);

        // Assert that the default classroom groups were created correctly.
        $this->assertTrue($classroom->defaultClassroomGroup()->exists());
        $this->assertEquals($attributes['pass_grade'], $classroom->defaultClassroomGroup->pass_grade);
        $this->assertEquals($attributes['attempts'], $classroom->defaultClassroomGroup->attempts);
    }

    /**
     * @see ClassroomService::update()
     */
    public function test_it_updates_a_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher1 = $this->fakeAdminTeacher($school);
        $teacher2 = $this->fakeAdminTeacher($school);
        $classroom = $this->fakeClassroom($teacher1, 1, [
            'name' => 'Old class name',
            'pass_grade' => 80,
            'attempts' => 2,
        ]);

        $attributes = [
            'name' => 'Updated class name',
            'owner_id' => $teacher2->id,
            'pass_grade' => 10,
            'attempts' => 1
        ];

        $result = $this->classroomService->update($classroom, $attributes);

        // Assert that it returns the updated classroom.
        $this->assertInstanceOf(Classroom::class, $result);
        $this->assertEquals($classroom->id, $result->id);
        $this->assertEquals($attributes['name'], $result->name);
        $this->assertEquals($attributes['owner_id'], $result->owner_id);
        $this->assertEquals($attributes['pass_grade'], $result->defaultClassroomGroup->pass_grade);
        $this->assertEquals($attributes['attempts'], $result->defaultClassroomGroup->attempts);

        // Assert that the classroom was updated correctly.
        $updatedClassroom = Classroom::find($classroom->id);
        $this->assertEquals($attributes['name'], $updatedClassroom->name);
        $this->assertEquals($attributes['owner_id'], $updatedClassroom->owner_id);
        $this->assertEquals($attributes['pass_grade'], $updatedClassroom->defaultClassroomGroup->pass_grade);
        $this->assertEquals($attributes['attempts'], $updatedClassroom->defaultClassroomGroup->attempts);
    }


    /**
     * @see ClassroomService::assignSecondaryTeachers()
     */
    public function test_it_adds_secondary_teachers_with_detaching_by_default()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher1 = $this->fakeNonAdminTeacher($school);
        $teacher2 = $this->fakeNonAdminTeacher($school);
        $teacher3 = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        // Assert that there is no secondary teacher associate with the classroom.
        $this->assertEquals(0, $classroom->secondaryTeachers()->count());

        // Add $teacher1 and $teacher 2 as secondary teachers.
        $this->classroomService->assignSecondaryTeachers($classroom, [$teacher1->id, $teacher2->id]);

        // Assert that there are 2 secondary teachers associate with the classroom.
        $this->assertEquals(2, $classroom->secondaryTeachers()->count());

        // Assert that $teacher1 and $teacher2 are the secondary teachers.
        $this->assertEquals([$teacher1->id, $teacher2->id], $classroom->secondaryTeachers()->pluck('teachers.id')->toArray());

        // Add $teacher2 and $teacher3 as secondary teachers.
        $this->classroomService->assignSecondaryTeachers($classroom, [$teacher2->id, $teacher3->id]);

        // Assert that there are 2 secondary teachers associate with the classroom.
        $this->assertEquals(2, $classroom->secondaryTeachers()->count());

        // Assert that $teacher2 and $teacher3 are the secondary teachers.
        $this->assertEquals([$teacher2->id, $teacher3->id], $classroom->secondaryTeachers()->pluck('teachers.id')->toArray());
    }

    /**
     * @see ClassroomService::assignSecondaryTeachers()
     */
    public function test_it_adds_secondary_teachers_without_detaching()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher1 = $this->fakeNonAdminTeacher($school);
        $teacher2 = $this->fakeNonAdminTeacher($school);
        $teacher3 = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        // Assert that there is no secondary teacher associate with the classroom.
        $this->assertEquals(0, $classroom->secondaryTeachers()->count());

        // Add $teacher1 and $teacher 2 as secondary teachers.
        $this->classroomService->assignSecondaryTeachers($classroom, [$teacher1->id, $teacher2->id]);

        // Assert that there are 2 secondary teachers associate with the classroom.
        $this->assertEquals(2, $classroom->secondaryTeachers()->count());

        // Assert that $teacher1 and $teacher2 are the secondary teachers.
        $this->assertEquals([$teacher1->id, $teacher2->id], $classroom->secondaryTeachers()->pluck('teachers.id')->toArray());

        // Add $teacher2 and $teacher3 as secondary teachers.
        $this->classroomService->assignSecondaryTeachers($classroom, [$teacher2->id, $teacher3->id], false);

        // Assert that there are 3 secondary teachers associate with the classroom.
        $this->assertEquals(3, $classroom->secondaryTeachers()->count());

        // Assert that $teacher2 and $teacher3 are the secondary teachers.
        $this->assertEquals([$teacher1->id, $teacher2->id, $teacher3->id], $classroom->secondaryTeachers()->pluck('teachers.id')->toArray());
    }


    /**
     * @see ClassroomService::removeSecondaryTeachers()
     */
    public function test_it_removes_secondary_teachers()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher1 = $this->fakeNonAdminTeacher($school);
        $teacher2 = $this->fakeNonAdminTeacher($school);
        $teacher3 = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        // Assert that there is no secondary teacher associate with the classroom.
        $this->assertEquals(0, $classroom->secondaryTeachers()->count());

        // Add $teacher1, $teacher2 and $teacher3 as secondary teachers.
        $this->classroomService->assignSecondaryTeachers($classroom, [$teacher1->id, $teacher2->id, $teacher3->id]);

        // Assert that there are 3 secondary teachers associate with the classroom.
        $this->assertEquals(3, $classroom->secondaryTeachers()->count());

        // Remove $teacher1 as secondary teachers.
        $this->classroomService->removeSecondaryTeachers($classroom, [$teacher1->id]);

        // Assert that there are 2 secondary teacher associate with the classroom.
        $this->assertEquals(2, $classroom->secondaryTeachers()->count());

        // Assert that the secondary teachers are $teacher2 and $teacher3.
        $this->assertDatabaseMissing('classroom_secondary_teacher', [
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher1->id,
        ])->assertDatabaseHas('classroom_secondary_teacher', [
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher2->id,
        ])->assertDatabaseHas('classroom_secondary_teacher', [
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher3->id,
        ]);

        // Remove $teacher1 and $teacher2 as secondary teachers.
        $this->classroomService->removeSecondaryTeachers($classroom, [$teacher1->id, $teacher2->id]);

        // Assert that there is 1 secondary teacher associate with the classroom.
        $this->assertEquals(1, $classroom->secondaryTeachers()->count());

        // Assert that the secondary teacher is $teacher3.
        $this->assertDatabaseMissing('classroom_secondary_teacher', [
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher1->id,
        ])->assertDatabaseMissing('classroom_secondary_teacher', [
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher2->id,
        ])->assertDatabaseHas('classroom_secondary_teacher', [
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher3->id,
        ]);
    }

    /**
     * @see ClassroomService::delete()
     */
    public function test_it_deletes_a_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $defaultClassroomGroup = $classroom->defaultClassroomGroup;
        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom);

        $this->classroomService->delete($classroom);

        // Assert that the classroom was soft-deleted.
        $this->assertSoftDeleted('classrooms', ['id' => $classroom->id]);

        // Assert that the classroom groups was soft-deleted.
        $this->assertSoftDeleted('classroom_groups', ['id' => $defaultClassroomGroup->id]);
        $this->assertSoftDeleted('classroom_groups', ['id' => $customClassroomGroup->id]);
    }

}
