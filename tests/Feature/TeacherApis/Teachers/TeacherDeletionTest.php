<?php

namespace Tests\Feature\TeacherApis\Teachers;

use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test teacher deletion endpoint for teachers.
 *
 * @see /routes/api/api-teachers.php
 * @see TeacherController::destroy()
 */
class TeacherDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_admins_can_delete_teachers_in_the_same_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $teacherAdmin = $this->createTeacherAdmin($school);

        $teacher = $this->createNonAdminTeacher($school);

        $this->actingAsTeacher($teacherAdmin);

        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', ['teacher' => $teacher]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
    }

    public function test_teacher_admins_are_unauthorised_to_delete_teachers_in_another_school()
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->createTraditionalSchool();
        $school2 = $this->createTraditionalSchool();

        $teacherAdmin = $this->createTeacherAdmin($school1);

        $teacher = $this->createNonAdminTeacher($school2);

        $this->actingAsTeacher($teacherAdmin);

        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', ['teacher' => $teacher]));

        $response->assertForbidden();

        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
    }

    public function test_non_admin_teacher_are_unauthorised_to_delete_teachers()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $nonAdminTeacher = $this->createNonAdminTeacher($school);

        $teacher = $this->createNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', ['teacher' => $teacher]));

        $response->assertForbidden();

        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
    }
}
