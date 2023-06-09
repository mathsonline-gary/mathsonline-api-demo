<?php

namespace Tests\Feature\TeacherApis\Teachers;

use App\Models\School;
use App\Models\Users\Teacher;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test teacher indexing endpoint for teachers.
 *
 * @see /routes/api/api-teachers.php
 * @see TeacherController::index()
 */
class IndexTeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_administrators_can_only_get_the_list_of_teachers_in_same_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->createTraditionalSchool();
        $school2 = $this->createTraditionalSchool();

        $teacherAdmin = $this->createTeacherAdmin($school1);
        $teachers1 = $this->createNonAdminTeacher($school1, 10);
        $this->createNonAdminTeacher($school2, 10);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->getJson(route('api.teachers.v1.teachers.index'));

        // Assert that the request is successful.
        $response->assertOk();

        // Assert that the response contains the correct number of teachers.
        $response->assertJsonCount($teachers1->count() + 1, 'data');

        // Assert the response has the expected attributes of each teacher.
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'username',
                    'first_name',
                    'last_name',
                    'is_admin',
                ],
            ]
        ]);

        // Assert the response does not contain the password of each teacher.
        $response->assertJsonMissingPath('data.*.password');

        // Assert all teachers in school 2 are not included.
        $response->assertJsonMissing([
            'school_id' => $school2->id,
        ]);
    }

    public function test_non_admin_teachers_are_unauthorised_to_get_the_list_of_teachers(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $nonAdminTeacher = $this->createNonAdminTeacher($school);
        $this->createNonAdminTeacher($school, 10);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.teachers.index'));

        // Assert that the request is unauthorised.
        $response->assertForbidden();
    }
}
