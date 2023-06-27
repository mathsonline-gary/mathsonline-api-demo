<?php

namespace Tests\Unit\Services;

use App\Models\Classroom;
use App\Services\ClassroomService;
use Database\Seeders\MarketSeeder;
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
     * @see ClassroomService::search().
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
     * @see ClassroomService::search().
     */
    public function test_it_fuzzy_searches_classrooms(): void
    {
        // TODO
    }

    public function test_it_returns_search_result_without_pagination(): void
    {
    }
}
