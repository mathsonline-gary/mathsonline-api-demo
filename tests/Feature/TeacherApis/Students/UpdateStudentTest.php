<?php

namespace Tests\Feature\TeacherApis\Students;

use App\Http\Controllers\Api\Teachers\V1\StudentController;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 * @see StudentController::update()
 */
class UpdateStudentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run MarketSeeder before each test.
     *
     * @var string
     */
    protected string $seeder = MarketSeeder::class;

    /**
     * The payload to use for updating the teacher.
     *
     * @var array
     */
    protected array $payload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payload = [
            'username' => fake()->userName,
            'email' => fake()->safeEmail,
            'password' => 'password',
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
        ];
    }

    public function test_an_admin_teacher_can_update_details_of_a_student_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response is successful with updated student profile.
        $response->assertOk()
            ->assertJsonFragment(Arr::except($this->payload, ['password']));
    }

    public function test_an_admin_teacher_cannot_update_details_of_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response has a 404 “Not Found” status code.
        $response->assertNotFound();
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_update_details_of_a_student_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    public function test_a_non_admin_teacher_cannot_update_details_of_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }
}
