<?php

namespace Tests\Feature\TeacherApis\Students;

use App\Http\Controllers\Api\Teachers\V1\StudentController;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see StudentController::destroy()
 */
class DeleteStudentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run MarketSeeder before each test.
     *
     * @var string
     */
    protected string $seeder = MarketSeeder::class;

    public function test_an_admin_teacher_can_delete_a_student_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.students.destroy', $student));

        $response->assertNoContent();
    }

    public function test_an_admin_teacher_is_unauthorized_to_delete_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.students.destroy', $student));

        // Assert that the response has a 404 “Not Found” status code.
        $response->assertNotFound();
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_delete_a_student(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.students.destroy', $student));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }
}
