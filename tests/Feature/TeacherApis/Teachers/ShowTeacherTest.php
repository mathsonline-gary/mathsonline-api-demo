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

        $school = $this->fakeTraditionalSchool();

        $teacherAdmin = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->getJson(route('api.teachers.v1.teachers.show', $teacher->id));

        // Assert that the request is successful.
        $response->assertOk();

        // Assert that the teacher profile is correct.
        $response->assertJsonFragment(['id' => $teacher->id]);
    }

    public function test_non_admin_teachers_are_unauthorised_to_get_the_profile_of_another_teacher_in_same_school(): void
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school = $this->fakeTraditionalSchool();

        $teacher1 = $this->fakeNonAdminTeacher($school);
        $teacher2 = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher1);

        $response = $this->getJson(route('api.teachers.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_teacher_administrators_are_unauthorized_to_get_the_profile_of_a_teacher_in_different_school(): void
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $teacher1 = $this->fakeAdminTeacher($school1);
        $teacher2 = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($teacher1);

        $response = $this->getJson(route('api.teachers.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_non_admin_teachers_are_unauthorised_to_get_the_profile_of_a_teacher_in_different_school(): void
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $teacher1 = $this->fakeNonAdminTeacher($school1);
        $teacher2 = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($teacher1);

        $response = $this->getJson(route('api.teachers.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }
}
