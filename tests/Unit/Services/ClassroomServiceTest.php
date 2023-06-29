<?php

namespace Tests\Unit\Services;

use App\Models\Classroom;
use App\Services\ClassroomService;
use Database\Seeders\MarketSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
use function PHPUnit\Framework\assertTrue;

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
        self::assertEquals($classroom->id, $result->id);

        // Assert that the loaded relationships are correct.
        assertTrue($result->relationLoaded('school'));
        assertTrue($result->relationLoaded('owner'));
        assertTrue($result->relationLoaded('secondaryTeachers'));
        assertTrue($result->relationLoaded('classroomGroups'));
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
