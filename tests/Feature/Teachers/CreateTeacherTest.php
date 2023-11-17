<?php

namespace Tests\Feature\Teachers;

use App\Enums\ActivityType;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Models\Activity;
use App\Models\Users\Teacher;
use Tests\TestCase;

/**
 * @see TeacherController::store()
 */
class CreateTeacherTest extends TestCase
{
    /**
     * The payload to use for creating the teacher.
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
            'position' => fake()->word,
            'title' => 'Mr',
            'is_admin' => fake()->boolean,
        ];
    }

    public function test_a_guest_is_unauthenticated_to_create_a_teacher()
    {
        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has a 401 “Unauthorized” status code.
        $response->assertUnauthorized();
    }

    public function test_an_admin_teacher_can_add_a_teacher_into_their_school()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the request is successful.
        $response->assertCreated()
            ->assertJsonSuccessful();

        // Assert that the new teacher was created in the database with correct data.
        $this->assertDatabaseCount('teachers', 2);
        $this->assertDatabaseCount('users', 2);
        $teacher = Teacher::latest('id')->first();
        $this->assertTeacherAttributes([
            ...$this->payload,
            'school_id' => $school->id,
            'deleted_at' => null,
        ], $teacher);
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_add_a_teacher()
    {
        $this->actingAsTeacher($this->fakeNonAdminTeacher());

        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    public function test_it_returns_expected_teacher_attributes()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has correct data of the new teacher.
        $response->assertJsonFragment([
            'school_id' => $school->id,
            'username' => $this->payload['username'],
            'email' => $this->payload['email'],
            'first_name' => $this->payload['first_name'],
            'last_name' => $this->payload['last_name'],
            'position' => $this->payload['position'],
            'title' => $this->payload['title'],
            'is_admin' => $this->payload['is_admin'],
        ])->assertJsonMissing(['password']);
    }

    public function test_it_logs_created_teacher_activity()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);

        $this->assertDatabaseCount('activities', 0);

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the activity was logged.
        $this->assertDatabaseCount('activities', 1);

        // Assert that the activity was logged with correct data.
        $activity = Activity::first();
        $teacher = Teacher::latest('id')->first();

        $this->assertEquals($adminTeacher->asUser()->id, $activity->actor_id);
        $this->assertEquals(ActivityType::CREATED_TEACHER, $activity->type);
        $this->assertArrayHasKey('id', $activity->data);
        $this->assertEquals($teacher->id, $activity->data['id']);
        $this->assertEquals($teacher->created_at, $activity->acted_at);
    }

    public function test_username_field_is_unique()
    {
        $adminTeacher = $this->fakeAdminTeacher();

        $this->actingAsTeacher($adminTeacher);

        $this->payload['username'] = $adminTeacher->username;

        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has a 422 “Unprocessable Entity” status code.
        $response->assertUnprocessable()
            ->assertInvalid('username');
    }

    public function test_username_field_is_required()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['username']);

        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has a 422 “Unprocessable Entity” status code.
        $response->assertUnprocessable()
            ->assertInvalid('username');
    }

    public function test_username_field_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the minimum length validation.
        $this->payload['username'] = 'a';
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['username' => __('validation.min.string', ['attribute' => 'username', 'min' => 3])]);

        // Test the maximum length validation.
        $this->payload['username'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['username' => __('validation.max.string', ['attribute' => 'username', 'max' => 32])]);
    }

    public function test_email_field_is_optional()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['email']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['email' => null]);

        $this->assertDatabaseHas('teachers', ['email' => null]);
    }

    public function test_email_field_is_nullable()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['email'] = null;

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['email' => null]);

        $this->assertDatabaseHas('teachers', ['email' => null]);
    }

    public function test_email_field_is_validated_as_email_address()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the email validation.
        $this->payload['email'] = 'invalid-email';
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['email' => __('validation.email', ['attribute' => 'email'])]);
    }

    public function test_email_field_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the minimum length validation.
        $this->payload['email'] = 'a@b';
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['email' => __('validation.min.string', ['attribute' => 'email', 'min' => 4])]);

        // Test the maximum length validation.
        $this->payload['email'] = str_repeat('a', 129) . '@' . str_repeat('b', 64) . '.' . str_repeat('c', 63);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['email' => __('validation.max.string', ['attribute' => 'email', 'max' => 128])]);
    }

    public function test_first_name_field_is_required()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['first_name']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['first_name' => __('validation.required', ['attribute' => 'first name'])]);
    }

    public function test_first_name_field_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the maximum length validation.
        $this->payload['first_name'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['first_name' => __('validation.max.string', ['attribute' => 'first name', 'max' => 32])]);
    }

    public function test_last_name_field_is_required()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['last_name']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['last_name' => __('validation.required', ['attribute' => 'last name'])]);
    }

    public function test_last_name_field_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the maximum length validation.
        $this->payload['last_name'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['last_name' => __('validation.max.string', ['attribute' => 'last name', 'max' => 32])]);
    }

    public function test_password_field_is_required()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['password']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['password' => __('validation.required', ['attribute' => 'password'])]);
    }

    public function test_password_field_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the minimum length validation.
        $this->payload['password'] = 'a';
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['password' => __('validation.min.string', ['attribute' => 'password', 'min' => 4])]);

        // Test the maximum length validation.
        $this->payload['password'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['password' => __('validation.max.string', ['attribute' => 'password', 'max' => 32])]);
    }

    public function test_title_field_is_optional()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['title']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['title' => null]);

        $this->assertDatabaseHas('teachers', ['title' => null]);
    }

    public function test_title_field_is_nullable()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['title'] = null;

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['title' => null]);

        $this->assertDatabaseHas('teachers', ['title' => null]);
    }

    public function test_title_field_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the maximum length validation.
        $this->payload['title'] = str_repeat('a', 17);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['title' => __('validation.max.string', ['attribute' => 'title', 'max' => 16])]);
    }

    public function test_position_field_is_optional()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['position']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['position' => null]);

        $this->assertDatabaseHas('teachers', ['position' => null]);
    }

    public function test_position_field_is_nullable()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['position'] = null;

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['position' => null]);

        $this->assertDatabaseHas('teachers', ['position' => null]);
    }

    public function test_position_field_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the maximum length validation.
        $this->payload['position'] = str_repeat('a', 129);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['position' => __('validation.max.string', ['attribute' => 'position', 'max' => 128])]);
    }

    public function test_is_admin_field_is_required()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['is_admin']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertUnprocessable()
            ->assertInvalid(['is_admin' => __('validation.required', ['attribute' => 'is admin'])]);
    }
}
