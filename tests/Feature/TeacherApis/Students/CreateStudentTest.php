<?php

namespace Tests\Feature\TeacherApis\Students;

use App\Http\Requests\StudentRequests\StoreStudentRequest;
use App\Models\Activity;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateStudentTest extends TestCase
{
    use RefreshDatabase;

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
    }

    /**
     * @see StudentController::store()
     */
    public function test_an_admin_teacher_can_create_a_student_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);

        $studentsCount = Student::count();
        $activitiesCount = Activity::count();

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

        // Assert that the student is created in the database.
        $this->assertDatabaseCount('students', $studentsCount + 1);
        $student = Student::first();
        $this->assertEquals($school->id, $student->school_id);
        $this->assertEquals($this->payload['username'], $student->username);
        $this->assertEquals($this->payload['email'], $student->email);
        $this->assertEquals($this->payload['first_name'], $student->first_name);
        $this->assertEquals($this->payload['last_name'], $student->last_name);
        $this->assertTrue(Hash::check($this->payload['password'], $student->password));

        // Assert that the activity is logged.
        $this->assertDatabaseCount('activities', $activitiesCount + 1);
        $activity = Activity::first();
        $this->assertEquals(Teacher::class, $activity->actable_type);
        $this->assertEquals($adminTeacher->id, $activity->actable_id);
        $this->assertEquals('created student', $activity->type);
        $this->assertArrayHasKey('student_id', $activity->data);
        $this->assertEquals($student->id, $activity->data['student_id']);
        $this->assertEquals($student->created_at, $activity->acted_at);
    }

    /**
     * @see StudentController::store()
     */
    public function test_an_admin_teacher_can_only_create_a_student_in_their_school()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $studentsCount = Student::count();
        $activitiesCount = Activity::count();

        $this->actingAsTeacher($adminTeacher);

        $this->payload['school_id'] = $school2->id;

        $response = $this->postJson(route('api.teachers.v1.students.store', $this->payload));

        // Assert that the response has a 201 “Created” status code and the student is created in $school1.
        $response->assertCreated()
            ->assertJsonFragment(['school_id' => $school1->id])
            ->assertJsonMissing(['password']);

        // Assert that the student is created with the correct school_id.
        $this->assertDatabaseCount('students', $studentsCount + 1);
        $student = Student::first();
        $this->assertEquals($school1->id, $student->school_id);

        // Assert that the activity is logged.
        $this->assertDatabaseCount('activities', $activitiesCount + 1);
        $activity = Activity::first();
        $this->assertEquals(Teacher::class, $activity->actable_type);
        $this->assertEquals($adminTeacher->id, $activity->actable_id);
        $this->assertEquals('created student', $activity->type);
        $this->assertArrayHasKey('student_id', $activity->data);
        $this->assertEquals($student->id, $activity->data['student_id']);
        $this->assertEquals($student->created_at, $activity->acted_at);
    }

    /**
     * @see StudentController::store()
     */
    public function test_a_non_admin_teacher_is_unauthorized_to_create_a_student(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $this->actingAsTeacher($nonAdminTeacher);

        $studentsCount = Student::count();
        $activitiesCount = Activity::count();

        $response = $this->postJson(route('api.teachers.v1.students.store', $this->payload));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that the student is not created in the database.
        $this->assertDatabaseCount('students', $studentsCount);

        // Assert that no activity was logged.
        $this->assertDatabaseCount('activities', $activitiesCount);
    }

    /**
     * @see StoreStudentRequest::rules()
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

    /**
     * @see StoreStudentRequest::rules()
     */
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

    /**
     * @see StoreStudentRequest::rules()
     */
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

    /**
     * @see StoreStudentRequest::rules()
     */
    public function test_username_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the min length of the username attribute is 3 characters.
        $this->payload['username'] = str_repeat('a', 2);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username');

        // Test that the max length of the username attribute is 32 characters.
        $this->payload['username'] = str_repeat('a', 33);
        $this->postJson(route('api.teachers.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username');
    }

    /**
     * @see StoreStudentRequest::rules()
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

    /**
     * @see StoreStudentRequest::rules()
     */
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

    /**
     * @see StoreStudentRequest::rules()
     */
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

    /**
     * @see StoreStudentRequest::rules()
     */
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

    /**
     * @see StoreStudentRequest::rules()
     */
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

    /**
     * @see StoreStudentRequest::rules()
     */
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

    /**
     * @see StoreStudentRequest::rules()
     */
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

    /**
     * @see StoreStudentRequest::rules()
     */
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

    /**
     * @see StoreStudentRequest::rules()
     */
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

    /**
     * @see StoreStudentRequest::rules()
     */
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
