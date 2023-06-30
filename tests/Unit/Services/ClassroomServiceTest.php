<?php

namespace Tests\Unit\Services;

use App\Models\Classroom;
use App\Models\ClassroomGroup;
use App\Services\ClassroomService;
use Database\Seeders\MarketSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * @see ClassroomService
 */
class ClassroomServiceTest extends TestCase
{
    use RefreshDatabase;

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
        $this->seed([MarketSeeder::class]);

        // Create classrooms in school 1.
        $school1 = $this->createTraditionalSchool();
        $teacher1 = $this->createAdminTeacher($school1);
        $this->createClassroom($teacher1, 5);

        // Create classrooms in school 2.
        $school2 = $this->createTraditionalSchool();
        $teacher2 = $this->createAdminTeacher($school2);
        $this->createClassroom($teacher2, 5);

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
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $owner = $this->createAdminTeacher($school);

        $classroom1 = $this->createClassroom($owner, 1, ['name' => 'Class 1']);
        $classroom2 = $this->createClassroom($owner, 1, ['name' => 'Classroom 2']);
        $classroom3 = $this->createClassroom($owner, 1, ['name' => 'Class 3']);

        $result1 = $this->classroomService->search(['key' => '1']);
        $result2 = $this->classroomService->search(['key' => 'room']);
        $result3 = $this->classroomService->search(['key' => 'class']);

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
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $owner = $this->createAdminTeacher($school);
        $classrooms = $this->createClassroom($owner, 30);

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
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $adminTeacher = $this->createAdminTeacher($school);
        $teachers = $this->createNonAdminTeacher($school, 2);
        $classroom = $this->createClassroom($adminTeacher);
        $this->createCustomClassroomGroup($classroom, 2);
        $this->addSecondaryTeachers($classroom, $teachers->pluck('id')->toArray());

        // Call find() method with default options.
        $result = $this->classroomService->find($classroom->id);

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
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $teacher = $this->createAdminTeacher($school);

        $attributes = [
            'school_id' => $school->id,
            'owner_id' => $teacher->id,
            'type' => Classroom::TRADITIONAL_CLASSROOM,
            'name' => 'Test Class',
            'pass_grade' => 80,
            'attempts' => 1,
        ];

        $classroom = $this->classroomService->create($attributes);

        // Assert that the classroom was created correctly.
        $this->assertInstanceOf(Classroom::class, $classroom);
        $this->assertEquals($attributes['school_id'], $classroom->school_id);
        $this->assertEquals($attributes['owner_id'], $classroom->owner_id);
        $this->assertEquals($attributes['type'], $classroom->type);
        $this->assertEquals($attributes['name'], $classroom->name);
        $this->assertEquals($attributes['pass_grade'], $classroom->pass_grade);
        $this->assertEquals($attributes['attempts'], $classroom->attempts);

        // Assert that the default classroom group was created correctly.
        $this->assertTrue($classroom->defaultClassroomGroup()->exists());
    }

    /**
     * @see ClassroomService::addDefaultGroup()
     */
    public function test_it_adds_default_classroom_group(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $teacher = $this->createAdminTeacher($school);
        $classroom = $this->createClassroom($teacher);

        // Remove existing default classroom group.
        $classroom->defaultClassroomGroup()->delete();

        // Assert that there is not default group of the classroom.
        $this->assertFalse($classroom->defaultClassroomGroup()->exists());

        // Add the default group for the classroom.
        $group = $this->classroomService->addDefaultGroup($classroom);

        // Assert that the default group was added correctly.
        $this->assertTrue($classroom->defaultClassroomGroup()->exists());
        $this->assertEquals($classroom->defaultClassroomGroup->id, $group->id);
        $this->assertInstanceOf(ClassroomGroup::class, $group);
        $this->assertStringContainsString($classroom->name, $group->name);
        $this->assertEquals($classroom->pass_grade, $group->pass_grade);
    }

    /**
     * @see ClassroomService::addDefaultGroup()
     */
    public function test_it_does_not_add_default_classroom_group_if_it_exists(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $teacher = $this->createAdminTeacher($school);
        $classroom = $this->createClassroom($teacher);

        // Assert that there is already a default group of the classroom.
        $this->assertTrue($classroom->defaultClassroomGroup()->exists());

        $defaultGroup = $classroom->defaultClassroomGroup;

        $group = $this->classroomService->addDefaultGroup($classroom);

        // Assert that no default classroom group added.
        $this->assertNull($group);
        $this->assertTrue($classroom->defaultClassroomGroup()->exists());
        $this->assertEquals($defaultGroup->id, $classroom->defaultClassroomGroup->id);
    }

    /**
     * @see ClassroomService::delete()
     */
    public function test_it_deletes_a_classroom(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $adminTeacher = $this->createAdminTeacher($school);
        $teachers = $this->createNonAdminTeacher($school, 2);

        $students = $this->createStudent($school, 5);

        $classroom = $this->createClassroom($adminTeacher);

        $this->addSecondaryTeachers($classroom, $teachers->pluck('id')->toArray());

        $defaultClassroomGroup = $classroom->defaultClassroomGroup;
        $customClassroomGroup = $this->createCustomClassroomGroup($classroom);

        $this->addStudentsToClassroomGroup($defaultClassroomGroup, $students->pluck('id')->toArray());
        $this->addStudentsToClassroomGroup($customClassroomGroup, [$students->first()->id]);

        $this->classroomService->delete($classroom);

        // Assert that the classroom was deleted.
        $this->assertDatabaseMissing('classrooms', ['id' => $classroom->id]);

        // Assert that there is no teacher related to the classroom
        $this->assertDatabaseMissing('classroom_secondary_teacher', ['classroom_id' => $classroom->id]);

        // Assert that the classroom groups was deleted.
        $this->assertDatabaseMissing('classrooms', ['id' => $defaultClassroomGroup->id]);
        $this->assertDatabaseMissing('classrooms', ['id' => $customClassroomGroup->id]);

        // Assert that there is no student associate with the classroom groups.
        $this->assertDatabaseMissing('classroom_group_student', ['id' => $defaultClassroomGroup->id]);
        $this->assertDatabaseMissing('classroom_group_student', ['id' => $customClassroomGroup->id]);
    }
}
