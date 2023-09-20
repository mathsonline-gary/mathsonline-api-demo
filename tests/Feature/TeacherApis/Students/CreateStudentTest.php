<?php

namespace Tests\Feature\TeacherApis\Students;

use App\Events\Students\StudentCreated;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
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
            'password_confirmation' => 'password',
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
        ];

        Event::fake();
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
        $response->assertJsonFragment([
            'school_id' => $school->id,
            'username' => $this->payload['username'],
            'email' => $this->payload['email'],
            'first_name' => $this->payload['first_name'],
            'last_name' => $this->payload['last_name'],
        ])->assertJsonMissing(['password']);

        // Assert that StudentCreated event is dispatched.
        Event::assertDispatched(StudentCreated::class, function ($event) use ($school, $adminTeacher) {
            return $event->actor->id === $adminTeacher->id &&
                $event->student->school_id === $school->id;
        });

        // Assert that the student is created in the database.
        $this->assertDatabaseHas('students', [
            'school_id' => $school->id,
            'username' => $this->payload['username'],
            'email' => $this->payload['email'],
            'first_name' => $this->payload['first_name'],
            'last_name' => $this->payload['last_name'],
        ]);
    }

    public function test_an_admin_teacher_can_only_create_a_student_in_their_school()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $this->actingAsTeacher($adminTeacher);

        $this->payload['school_id'] = $school2->id;

        $response = $this->postJson(route('api.teachers.v1.students.store', $this->payload));

        // Assert that the response has a 201 “Created” status code and the student is created in $school1.
        $response->assertCreated()
            ->assertJsonFragment(['school_id' => $school1->id])
            ->assertJsonMissing(['password']);

        // Assert that StudentCreated event is dispatched.
        Event::assertDispatched(StudentCreated::class, function ($event) use ($school1, $adminTeacher) {
            return $event->actor->id === $adminTeacher->id &&
                $event->student->school_id === $school1->id;
        });

        // Assert that the student is created in the database.
        $this->assertDatabaseHas('students', [
            'school_id' => $school1->id,
            'username' => $this->payload['username'],
            'email' => $this->payload['email'],
            'first_name' => $this->payload['first_name'],
            'last_name' => $this->payload['last_name'],
        ]);

        // Assert that the student is not created in $school2.
        $this->assertDatabaseMissing('students', [
            'school_id' => $school2->id,
            'username' => $this->payload['username'],
        ]);
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

    /**
     * @see
     */
    public function test_username_is_required(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the username attribute is required.
        unset($this->payload['username']);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username');
    }

    public function test_username_must_be_string_and_trimmed(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the username attribute must be a string, and it trims whitespace.
        $this->payload['username'] = '  ';
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username');
    }

    public function test_username_must_be_unique(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the username attribute must be unique in the school.
        $this->payload['username'] = $this->fakeStudent($school)->username;
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username');
    }

    public function test_username_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the username attribute must be between 1 and 32 characters.
        $this->payload['username'] = str_repeat('a', 33);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username');
    }

    /**
     * @see
     */
    public function test_email_is_optional(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the email attribute is optional.
        unset($this->payload['email']);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertCreated();
    }

    public function test_email_is_trimmed(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test it trims whitespace.
        $this->payload['email'] = '  test@test.com  ';
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['email' => 'test@test.com']);
    }

    public function test_email_must_be_valid(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the email attribute must be a valid email.
        $this->payload['email'] = 'test';
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('email');
    }

    public function test_first_name_is_required(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the first_name attribute is required.
        unset($this->payload['first_name']);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('first_name');
    }

    public function test_first_name_must_be_string_and_trimmed(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the first_name attribute must be a string, and it trims whitespace.
        $this->payload['first_name'] = '  ';
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('first_name');
    }

    public function test_first_name_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the first_name attribute must be between 1 and 32 characters.
        $this->payload['first_name'] = str_repeat('a', 33);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('first_name');
    }

    public function test_last_name_is_required(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the last_name attribute is required.
        unset($this->payload['last_name']);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('last_name');
    }

    public function test_last_name_must_be_string_and_trimmed(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the last_name attribute must be a string, and it trims whitespace.
        $this->payload['last_name'] = '  ';
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('last_name');
    }

    public function test_last_name_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the last_name attribute must be between 1 and 32 characters.
        $this->payload['last_name'] = str_repeat('a', 33);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('last_name');
    }

    public function test_password_is_required(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the password attribute is required.
        unset($this->payload['password']);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password');
    }

    public function test_password_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the password attribute must be between 4 and 32 characters.
        $this->payload['password'] = 'abc'; // Too short (3 characters).
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password');

        $this->payload['password'] = str_repeat('a', 33); // Too long (33 characters).
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password');
    }
}
