<?php

namespace Tests\Feature\TeacherApis\Teachers;

use App\Http\Controllers\Api\Teachers\V1\TeacherController;
use App\Models\School;
use App\Models\Users\Teacher;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test teacher showing endpoint for teachers.
 *
 * @see /routes/api/api-teachers.php
 * @see TeacherController::show()
 */
class ShowTeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_administrators_can_get_the_profile_of_a_teacher_in_same_school(): void
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school = $this->createTraditionalSchool();

        $teacherAdmin = $this->createAdminTeacher($school);
        $teacher = $this->createNonAdminTeacher($school);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->get(route('api.teachers.v1.teachers.show', $teacher->id));

        // Assert that the request is successful.
        $response->assertOk();

        // Assert that the teacher profile is correct.
        $response->assertJsonFragment([
            'id' => $teacher->id,
        ]);
    }

    public function test_non_admin_teachers_are_unauthorised_to_get_the_profile_of_another_teacher_in_same_school(): void
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school = $this->createTraditionalSchool();

        $teacher1 = $this->createNonAdminTeacher($school);
        $teacher2 = $this->createNonAdminTeacher($school);

        $this->actingAsTeacher($teacher1);

        $response = $this->get(route('api.teachers.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_teacher_administrators_are_unauthorized_to_get_the_profile_of_a_teacher_in_different_school(): void
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school1 = $this->createTraditionalSchool();
        $school2 = $this->createTraditionalSchool();

        $teacher1 = $this->createAdminTeacher($school1);
        $teacher2 = $this->createNonAdminTeacher($school2);

        $this->actingAsTeacher($teacher1);

        $response = $this->get(route('api.teachers.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_non_admin_teachers_are_unauthorised_to_get_the_profile_of_a_teacher_in_different_school(): void
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school1 = $this->createTraditionalSchool();
        $school2 = $this->createTraditionalSchool();

        $teacher1 = $this->createNonAdminTeacher($school1);
        $teacher2 = $this->createNonAdminTeacher($school2);

        $this->actingAsTeacher($teacher1);

        $response = $this->get(route('api.teachers.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }
}
