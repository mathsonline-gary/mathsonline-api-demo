<?php

namespace Tests\Feature\TeacherApis\Teachers;

use App\Models\School;
use App\Models\Users\Teacher;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherIndexingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run MarketSeeder before each test.
     *
     * @var string
     * @see MarketSeeder
     */
    protected string $seeder = MarketSeeder::class;

    public function test_teacher_administrators_can_only_get_the_list_of_teachers_in_same_school(): void
    {
        // Create a test school 1.
        $school1 = School::factory()
            ->traditionalSchool()
            ->create();

        // Create a test teacher admin in school 1.
        $teacherAdmin = Teacher::factory()
            ->admin()
            ->ofSchool($school1)
            ->create();

        // Create test teachers in school 1.
        $teachers1 = Teacher::factory()
            ->count(10)
            ->ofSchool($school1)
            ->create();

        // Create test school 2.
        $school2 = School::factory()
            ->traditionalSchool()
            ->create();

        // Create test teachers in school 2.
        Teacher::factory()
            ->count(10)
            ->ofSchool($school2)
            ->create();

        // Authenticate as the teacher admin.
        $this->actingAs($teacherAdmin, 'teacher');

        // Send request
        $response = $this->getJson(route('api.teachers.v1.teachers.index'));

        // Assert that the request is successful.
        $response->assertOk();

        // Assert that the response contains the correct number of teachers.
        $response->assertJsonCount($teachers1->count() + 1, 'data');

        // Assert the response has the expected attributes of each teacher.
        $response->assertJsonFragment([
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
            'data' => [
                '*' => [
                    'school_id' => $school2->id,
                ],
            ],
        ]);
    }

    public function test_non_admin_teachers_are_unauthorised_to_get_the_list_of_teachers(): void
    {
        // Create a test school.
        $school = School::factory()
            ->traditionalSchool()
            ->create();

        // Create a non-admin teacher in the school.
        $nonAdminTeacher = Teacher::factory()
            ->ofSchool($school)
            ->create();

        // Create test teachers in the school.
        Teacher::factory()
            ->count(10)
            ->ofSchool($school)
            ->create();

        // Authenticate as the non-admin teacher.
        $this->actingAs($nonAdminTeacher, 'teacher');

        // Send request
        $response = $this->getJson(route('api.teachers.v1.teachers.index'));

        // Assert that the request is unauthorised.
        $response->assertForbidden();
    }
}
