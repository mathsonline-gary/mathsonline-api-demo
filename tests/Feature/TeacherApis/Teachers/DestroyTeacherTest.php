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
class DestroyTeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_admins_can_delete_teachers_in_the_same_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $teacherAdmin = $this->createTeacherAdmin($school);

        $teachers = $this->createNonAdminTeacher($school, 10);

        // Assert 'teachers' database table contains the correct number of teachers
        $this->assertDatabaseCount('teachers', $teachers->count() + 1);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', [
            'ids' => $teachers->pluck('id')->toArray()
        ]));

        // Assert the response returns no content
        $response->assertNoContent();

        // Assert all teachers was deleted, except to $teacherAdmin
        $this->assertDatabaseCount('teachers', 1);
        $this->assertDatabaseHas('teachers', ['id' => $teacherAdmin->id]);
    }

    public function test_teacher_admins_are_unauthorised_to_delete_teachers_in_another_school()
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->createTraditionalSchool();
        $school2 = $this->createTraditionalSchool();

        $teacherAdmin = $this->createTeacherAdmin($school1);

        $teachers = $this->createNonAdminTeacher($school2, 10);

        // Assert that the number of teachers stored in the database is correct
        $this->assertDatabaseCount('teachers', $teachers->count() + 1);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', [
            'ids' => $teachers->pluck('id')->toArray()
        ]));

        // Assert that the request is unauthorised
        $response->assertForbidden();

        // Assert that there is no teacher deleted
        $this->assertDatabaseCount('teachers', $teachers->count() + 1);
    }

    public function test_non_admin_teacher_are_unauthorised_to_delete_teachers()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $nonAdminTeacher = $this->createNonAdminTeacher($school);

        $teachers = $this->createNonAdminTeacher($school, 10);

        // Assert that the number of teachers stored in the database is correct
        $this->assertDatabaseCount('teachers', $teachers->count() + 1);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', [
            'ids' => $teachers->pluck('id')->toArray()
        ]));

        // Assert that the request is unauthorised
        $response->assertForbidden();

        // Assert that there is no teacher deleted
        $this->assertDatabaseCount('teachers', $teachers->count() + 1);
    }
}
