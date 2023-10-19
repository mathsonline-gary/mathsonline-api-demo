<?php

namespace Feature\Students;

use App\Enums\ActivityType;
use App\Enums\UserType;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Middleware\SetAuthenticationDefaults;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Models\Activity;
use App\Models\Users\Student;
use App\Models\Users\StudentSetting;
use App\Models\Users\User;
use App\Policies\StudentPolicy;
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
            'expired_tasks_excluded' => fake()->boolean,
            'confetti_enabled' => fake()->boolean,
        ];
    }

    /**
     * Authorization test.
     *
     * @see SetAuthenticationDefaults::handle()
     */
    public function test_a_guest_is_unauthenticated_to_create_a_student()
    {
        $response = $this->postJson(route('api.v1.students.store', $this->payload));

        // Assert that the response has a 401 “Unauthorized” status code.
        $response->assertUnauthorized();
    }

    /**
     * Authorization test.
     *
     * @see StudentPolicy::create()
     * @see StudentController::store()
     */
    public function test_an_admin_teacher_can_create_a_student_in_their_school(): void
    {
        $adminTeacher = $this->fakeAdminTeacher();

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.v1.students.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated()
            ->assertJsonFragment(['success' => true]);
    }

    /**
     * Authorization test.
     *
     * @see StudentPolicy::create()
     * @see StudentController::store()
     */
    public function test_an_admin_teacher_can_only_create_a_student_in_their_school()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['school_id'] = $school2->id;

        $response = $this->postJson(route('api.v1.students.store', $this->payload));

        // Assert that the response has a 201 “Created” status code and the student is created in $school1.
        $response->assertCreated()
            ->assertJsonFragment(['success' => true])
            ->assertJsonFragment(['school_id' => $school1->id]);
    }

    /**
     * Authorization test.
     *
     * @see StudentPolicy::create()
     */
    public function test_a_non_admin_teacher_is_unauthorized_to_create_a_student(): void
    {
        $nonAdminTeacher = $this->fakeNonAdminTeacher();

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->postJson(route('api.v1.students.store', $this->payload));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    public function test_it_returns_the_created_student()
    {
        $adminTeacher = $this->fakeAdminTeacher();

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.v1.students.store', $this->payload));

        // Assert that the response contains the student.
        $response->assertCreated()
            ->assertJsonFragment(['success' => true])
            ->assertJsonFragment(['username' => $this->payload['username']])
            ->assertJsonFragment(['email' => $this->payload['email']])
            ->assertJsonFragment(['first_name' => $this->payload['first_name']])
            ->assertJsonFragment(['last_name' => $this->payload['last_name']])
            ->assertJsonMissing(['password']);
    }

    public function test_it_creates_the_student()
    {
        $adminTeacher = $this->fakeAdminTeacher();

        $studentCount = Student::count();
        $userCount = User::count();
        $studentSettingCount = StudentSetting::count();

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route('api.v1.students.store', $this->payload));

        // Assert that the student is created in the database.
        $this->assertDatabaseCount('students', $studentCount + 1);

        // Assert that the student is created correctly in the database.
        $student = Student::latest()->first();
        $this->assertEquals($this->payload['username'], $student->username);
        $this->assertEquals($this->payload['email'], $student->email);
        $this->assertEquals($this->payload['first_name'], $student->first_name);
        $this->assertEquals($this->payload['last_name'], $student->last_name);
        $this->assertEquals($adminTeacher->school_id, $student->school_id);

        // Assert that the associated user is created in the database.
        $this->assertDatabaseCount('users', $userCount + 1);

        // Assert that the associated user is created correctly in the database.
        $user = $student->asUser();
        $this->assertEquals($this->payload['username'], $user->login);
        $this->assertTrue(Hash::check($this->payload['password'], $user->password));
        $this->assertEquals(UserType::TYPE_STUDENT, $user->type);

        // Assert that the associated student setting is created in the database.
        $this->assertDatabaseCount('student_settings', $studentSettingCount + 1);

        // Assert that the associated student setting is created correctly in the database.
        $studentSetting = $student->settings;
        $this->assertEquals($student->id, $studentSetting->student_id);
        $this->assertEquals($this->payload['expired_tasks_excluded'], $studentSetting->expired_tasks_excluded);
        $this->assertEquals($this->payload['confetti_enabled'], $studentSetting->confetti_enabled);
    }

    public function test_it_logs_student_created_activity()
    {
        $adminTeacher = $this->fakeAdminTeacher();

        $activityCount = Activity::count();

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route('api.v1.students.store', $this->payload));

        // Assert that the student created activity is created in the database.
        $this->assertDatabaseCount('activities', $activityCount + 1);

        // Assert that the student created activity is created correctly in the database.
        $activity = Activity::latest('acted_at')->first();
        $student = Student::latest()->first();
        $this->assertEquals($adminTeacher->asUser()->id, $activity->actor_id);
        $this->assertEquals(ActivityType::CREATED_STUDENT, $activity->type);
        $this->assertArrayHasKey('id', $activity->data);
        $this->assertEquals($student->id, $activity->data['id']);
        $this->assertEquals($student->created_at, $activity->acted_at);
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_username_is_required(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the username attribute is required.
        unset($this->payload['username']);

        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username', __('validation.required', ['attribute' => 'username']));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_username_must_be_string_and_trimmed(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the username attribute must be a string, and it trims whitespace.
        $this->payload['username'] = '  ';

        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username', __('validation.required', ['attribute' => 'username']));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_username_must_be_unique(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the username attribute must be unique in the school.
        $this->payload['username'] = $this->fakeStudent($school)->username;

        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username', __('validation.unique', ['attribute' => 'username']));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_username_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the min length of the username attribute is 3 characters.
        $this->payload['username'] = str_repeat('a', 2);
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username');

        // Test that the max length of the username attribute is 32 characters.
        $this->payload['username'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username');
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_email_is_optional(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the email attribute is optional.
        unset($this->payload['email']);

        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['success' => true])
            ->assertJsonFragment(['email' => null]);

        // Assert that the email is null in the database.
        $this->assertDatabaseHas('students', ['email' => null]);
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_email_is_trimmed(): void
    {
        $adminTeacher = $this->fakeAdminTeacher();

        $this->actingAsTeacher($adminTeacher);

        // Test it trims whitespace.
        $this->payload['email'] = '  test@test.com  ';
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['success' => true])
            ->assertJsonFragment(['email' => 'test@test.com']);

        // Assert that the email is trimmed in the database.
        $this->assertDatabaseHas('students', ['email' => 'test@test.com']);
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_email_must_be_valid(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the email attribute must be a valid email.
        $this->payload['email'] = 'test';
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('email', __('validation.email', ['attribute' => 'email']));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_first_name_is_required(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the first_name attribute is required.
        unset($this->payload['first_name']);
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('first_name', __('validation.required', ['attribute' => 'first name']));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_first_name_must_be_string_and_trimmed(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the first_name attribute must be a string, and it trims whitespace.
        $this->payload['first_name'] = '  ';
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('first_name', __('validation.required', ['attribute' => 'first name']));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_first_name_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the first_name attribute must be between 1 and 32 characters.
        $this->payload['first_name'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('first_name', __('validation.max.string', ['attribute' => 'first name', 'max' => 32]));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_last_name_is_required(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the last_name attribute is required.
        unset($this->payload['last_name']);
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('last_name', __('validation.required', ['attribute' => 'last name']));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_last_name_must_be_string_and_trimmed(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the last_name attribute must be a string, and it trims whitespace.
        $this->payload['last_name'] = '  ';
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('last_name', __('validation.required', ['attribute' => 'last name']));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_last_name_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the last_name attribute must be between 1 and 32 characters.
        $this->payload['last_name'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('last_name', __('validation.max.string', ['attribute' => 'last name', 'max' => 32]));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_password_is_required(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the password attribute is required.
        unset($this->payload['password']);
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password', __('validation.required', ['attribute' => 'password']));
    }

    /**
     * Validation test.
     *
     * @see StoreStudentRequest::rules()
     */
    public function test_password_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->actingAsTeacher($adminTeacher);

        // Test that the min length of the password attribute is 4 characters.
        $this->payload['password'] = fake()->password(3);
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password', __('validation.min.string', ['attribute' => 'password', 'min' => 4]));

        // Test that the max length of the password attribute is 32 characters.
        $this->payload['password'] = fake()->password(33);
        $this->postJson(route('api.v1.students.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password', __('validation.max.string', ['attribute' => 'password', 'max' => 32]));
    }

}
