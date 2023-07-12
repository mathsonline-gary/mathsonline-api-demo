<?php

namespace Tests\Feature\TeacherApis\Students;

use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 * @see StudentController::store()
 */
class CreateStudentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run MarketSeeder before each test.
     *
     * @var string
     */
    protected string $seeder = MarketSeeder::class;

    /**
     * The payload to use for creating the student.
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

    public function test_an_admin_teacher_can_create_a_student_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.teachers.v1.students.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();

        // Assert that the response contains the created student.
        $response->assertJsonFragment(Arr::except($this->payload, ['password']));
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_create_a_student(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->postJson(route('api.teachers.v1.students.store', $this->payload));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }
}
