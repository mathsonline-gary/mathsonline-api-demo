<?php

namespace Tests\Integration\Users;

use App\Models\School;
use App\Models\Users\Teacher;
use App\Services\TeacherService;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * This testing class is used to test methods in TeacherService.
 *
 * @see TeacherService
 */
class TeacherServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TeacherService $teacherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherService = new TeacherService();
    }

    /**
     * Test the TeacherService can find a teacher by teacher ID with specified options.
     *
     * @return void
     *
     * @see TeacherService::find()
     *
     * @test
     */
    public function it_can_find_a_teacher(): void
    {
        $this->seed([
            MarketSeeder::class
        ]);
        
        $school = School::factory()->create([
            'market_id' => 1,
            'type' => 'traditional school',
        ]);

        $teacher = Teacher::factory()
            ->ofSchool($school)
            ->create();

        // Call the find method with options
        $foundTeacher = $this->teacherService->find($teacher->id, [
            'with_school' => true,
            'with_classrooms' => true,
        ]);

        // Assert that the teacher was found
        $this->assertInstanceOf(Teacher::class, $foundTeacher);

        // Assert that the found teacher's ID is correct
        $this->assertEquals($foundTeacher->id, $teacher->id);

        // Assert that the loaded relationships are correct
        $this->assertTrue($foundTeacher->relationLoaded('school'));
        $this->assertTrue($foundTeacher->relationLoaded('classroomsAsOwner'));
        $this->assertTrue($foundTeacher->relationLoaded('classroomsAsSecondaryTeacher'));
    }
}
