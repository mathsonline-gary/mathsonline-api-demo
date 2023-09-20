<?php

namespace Tests\Feature\TeacherApis\Students;

use App\Events\Students\StudentUpdated;
use App\Http\Controllers\Api\Teachers\V1\StudentController;
use App\Http\Requests\StudentRequests\UpdateStudentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateStudentTest extends TestCase
{
    use RefreshDatabase;

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

        Event::fake();
    }

    /**
     * @see StudentController::update()
     */
    public function test_an_admin_teacher_can_update_details_of_a_student_in_the_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response is successful with updated student profile.
        $response->assertOk()
            ->assertJsonFragment(Arr::except($this->payload, ['password']));

        // Assert that StudentUpdated event is dispatched.
        Event::assertDispatched(StudentUpdated::class, function (StudentUpdated $event) use ($adminTeacher, $student) {
            return $event->actor->is($adminTeacher)
                && $event->before['username'] === $student->username
                && $event->after->is($student)
                && $event->after->username === $this->payload['username'];
        });

        // Assert that the student is updated in the database.
        $student->refresh();
        $this->assertEquals($this->payload['username'], $student->username);
        $this->assertEquals($this->payload['email'], $student->email);
        $this->assertEquals($this->payload['first_name'], $student->first_name);
        $this->assertEquals($this->payload['last_name'], $student->last_name);
        $this->assertTrue(Hash::check($this->payload['password'], $student->password));

        // Assert that no new student is created in the database.
        $this->assertDatabaseCount('students', 1);
    }

    /**
     * @see StudentController::update()
     */
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

        // Assert that StudentUpdated event is dispatched.
        Event::assertNotDispatched(StudentUpdated::class);

        // Assert that the student is not updated in the database.
        $this->assertDatabaseCount('students', 1)
            ->assertDatabaseHas('students', $student->getAttributes());
    }

    /**
     * @see StudentController::update()
     */
    public function test_a_non_admin_teacher_is_unauthorized_to_update_details_of_a_student_in_the_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that StudentUpdated event is dispatched.
        Event::assertNotDispatched(StudentUpdated::class);

        // Assert that the student is not updated in the database.
        $this->assertDatabaseCount('students', 1)
            ->assertDatabaseHas('students', $student->getAttributes());
    }

    /**
     * @see StudentController::update()
     */
    public function test_a_non_admin_teacher_is_unauthorized_to_update_details_of_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that StudentUpdated event is dispatched.
        Event::assertNotDispatched(StudentUpdated::class);

        // Assert that the student is not updated in the database.
        $this->assertDatabaseCount('students', 1)
            ->assertDatabaseHas('students', $student->getAttributes());
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_username_is_optional()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        unset($this->payload['username']);

        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['username' => $student->username]);
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_username_must_be_unique()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['username'] = $this->fakeStudent($school)->username;

        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_username_length_validation()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        // Test that the minimum length of a username is 3.
        $this->payload['username'] = str_repeat('a', 2);
        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');

        // Test that the maximum length of a username is 32.
        $this->payload['username'] = str_repeat('a', 33);
        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_email_is_optional()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        unset($this->payload['email']);

        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['email' => $student->email]);
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_email_must_be_valid_email_address()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['email'] = 'invalid-email';

        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_first_name_is_optional()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        unset($this->payload['first_name']);

        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['first_name' => $student->first_name]);
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_first_name_is_trimmed()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['first_name'] = ' ' . $this->payload['first_name'] . ' ';

        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['first_name' => trim($this->payload['first_name'])]);
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_first_name_length_validation()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        // The minimum length of a first name is 1.
        $this->payload['first_name'] = '';
        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('first_name');

        // The maximum length of a first name is 32.
        $this->payload['first_name'] = str_repeat('a', 33);
        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('first_name');
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_last_name_is_optional()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        unset($this->payload['last_name']);

        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['last_name' => $student->last_name]);
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_last_name_is_trimmed()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['last_name'] = ' ' . $this->payload['last_name'] . ' ';

        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['last_name' => trim($this->payload['last_name'])]);
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_last_name_length_validation()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        // The minimum length of a last name is 1.
        $this->payload['last_name'] = '';
        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('last_name');

        // The maximum length of a last name is 32.
        $this->payload['last_name'] = str_repeat('a', 33);
        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('last_name');
    }

    /**
     * @see UpdateStudentRequest::rules()
     */
    public function test_password_is_optional()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);
        $oldPassword = $student->password;

        $this->actingAsTeacher($adminTeacher);

        unset($this->payload['password']);

        $this->putJson(route('api.teachers.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk();

        $student->refresh();
        $this->assertEquals($oldPassword, $student->password);
    }

    /**
     * @see StoreStudentRequest::rules()
     */
    public function test_password_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the min length of the password attribute is 4 characters.
        $this->payload['password'] = fake()->password(3);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password');

        // Test that the max length of the password attribute is 32 characters.
        $this->payload['password'] = fake()->password(33);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password');
    }
}
