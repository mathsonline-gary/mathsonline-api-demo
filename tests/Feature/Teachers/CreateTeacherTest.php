<?php

namespace Feature\Teachers;

use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Middleware\SetAuthenticationDefaults;
use App\Http\Requests\TeacherRequests\StoreTeacherRequest;
use App\Models\Activity;
use App\Models\Users\Teacher;
use App\Policies\TeacherPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * @see TeacherController::store()
 */
class CreateTeacherTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * Authentication test.
     *
     * @see SetAuthenticationDefaults
     */
    public function test_a_guest_is_unauthenticated_to_create_a_teacher()
    {
        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has a 401 “Unauthorized” status code.
        $response->assertUnauthorized();
    }

    /**
     * Authorization & Operation test.
     *
     * @see TeacherPolicy::create()
     * @see TeacherController::store()
     */
    public function test_an_admin_teacher_can_add_a_teacher_into_their_school()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);

        $teachersCount = Teacher::count();
        $activitiesCount = Activity::count();

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();

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

        // Assert that the teacher was created in the database.
        $this->assertDatabaseCount('teachers', $teachersCount + 1);

        // Assert that the teacher was created in the database with correct data.
        $teacher = Teacher::latest('id')->first();
        $this->assertEquals($school->id, $teacher->school_id);
        $this->assertEquals($this->payload['username'], $teacher->username);
        $this->assertEquals($this->payload['email'], $teacher->email);
        $this->assertEquals($this->payload['first_name'], $teacher->first_name);
        $this->assertEquals($this->payload['last_name'], $teacher->last_name);
        $this->assertEquals($this->payload['position'], $teacher->position);
        $this->assertEquals($this->payload['title'], $teacher->title);
        $this->assertEquals($this->payload['is_admin'], $teacher->is_admin);
        $this->assertTrue(Hash::check($this->payload['password'], $teacher->asUser()->password));

        // Assert that the activity was logged.
        $this->assertDatabaseCount('activities', $activitiesCount + 1);
        $activity = Activity::latest('id')->first();
        $this->assertEquals($adminTeacher->asUser()->id, $activity->actor_id);
        $this->assertEquals('created teacher', $activity->type);
        $this->assertArrayHasKey('teacher_id', $activity->data);
        $this->assertEquals($teacher->id, $activity->data['teacher_id']);
        $this->assertEquals($teacher->created_at, $activity->acted_at);
    }

    /**
     * Authorization test.
     *
     * @see TeacherPolicy::create()
     */
    public function test_a_non_admin_teacher_is_unauthorized_to_add_a_teacher()
    {
        $this->actingAsTeacher($this->fakeNonAdminTeacher());

        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_username_is_unique()
    {
        $adminTeacher = $this->fakeAdminTeacher();

        $this->actingAsTeacher($adminTeacher);

        $this->payload['username'] = $adminTeacher->username;

        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has a 422 “Unprocessable Entity” status code.
        $response->assertStatus(422)
            ->assertInvalid('username');
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_username_is_required()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['username']);

        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has a 422 “Unprocessable Entity” status code.
        $response->assertStatus(422)
            ->assertInvalid('username');
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_username_is_trimmed()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['username'] = ' ' . $this->payload['username'] . ' ';

        $response = $this->postJson(route('api.v1.teachers.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();

        // Assert that the response has correct data of the new teacher.
        $response->assertJsonFragment([
            'username' => trim($this->payload['username']),
        ]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_username_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the minimum length validation.
        $this->payload['username'] = 'a';
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['username' => __('validation.min.string', ['attribute' => 'username', 'min' => 3])]);

        // Test the maximum length validation.
        $this->payload['username'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['username' => __('validation.max.string', ['attribute' => 'username', 'max' => 32])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_email_is_optional()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['email']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['email' => null]);

        $this->assertDatabaseHas('teachers', ['email' => null]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_email_is_nullable()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['email'] = null;

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['email' => null]);

        $this->assertDatabaseHas('teachers', ['email' => null]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_email_is_validated_as_email_address()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the email validation.
        $this->payload['email'] = 'invalid-email';
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['email' => __('validation.email', ['attribute' => 'email'])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_email_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the minimum length validation.
        $this->payload['email'] = 'a@b';
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['email' => __('validation.min.string', ['attribute' => 'email', 'min' => 4])]);

        // Test the maximum length validation.
        $this->payload['email'] = str_repeat('a', 129) . '@' . str_repeat('b', 64) . '.' . str_repeat('c', 63);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['email' => __('validation.max.string', ['attribute' => 'email', 'max' => 128])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_first_name_is_required()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['first_name']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['first_name' => __('validation.required', ['attribute' => 'first name'])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_first_name_is_trimmed()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['first_name'] = ' ' . $this->payload['first_name'] . ' ';

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['first_name' => trim($this->payload['first_name'])]);

        $this->assertDatabaseHas('teachers', ['first_name' => trim($this->payload['first_name'])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_first_name_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the maximum length validation.
        $this->payload['first_name'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['first_name' => __('validation.max.string', ['attribute' => 'first name', 'max' => 32])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_last_name_is_required()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['last_name']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['last_name' => __('validation.required', ['attribute' => 'last name'])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_last_name_is_trimmed()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['last_name'] = ' ' . $this->payload['last_name'] . ' ';

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['last_name' => trim($this->payload['last_name'])]);

        $this->assertDatabaseHas('teachers', ['last_name' => trim($this->payload['last_name'])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_last_name_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the maximum length validation.
        $this->payload['last_name'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['last_name' => __('validation.max.string', ['attribute' => 'last name', 'max' => 32])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_password_is_required()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['password']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['password' => __('validation.required', ['attribute' => 'password'])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_password_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the minimum length validation.
        $this->payload['password'] = 'a';
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['password' => __('validation.min.string', ['attribute' => 'password', 'min' => 4])]);

        // Test the maximum length validation.
        $this->payload['password'] = str_repeat('a', 33);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['password' => __('validation.max.string', ['attribute' => 'password', 'max' => 32])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_title_is_optional()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['title']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['title' => null]);

        $this->assertDatabaseHas('teachers', ['title' => null]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_title_is_nullable()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['title'] = null;

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['title' => null]);

        $this->assertDatabaseHas('teachers', ['title' => null]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_title_is_trimmed()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['title'] = ' ' . $this->payload['title'] . ' ';

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['title' => trim($this->payload['title'])]);

        $this->assertDatabaseHas('teachers', ['title' => trim($this->payload['title'])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_title_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the maximum length validation.
        $this->payload['title'] = str_repeat('a', 17);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['title' => __('validation.max.string', ['attribute' => 'title', 'max' => 16])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_position_is_optional()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['position']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['position' => null]);

        $this->assertDatabaseHas('teachers', ['position' => null]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_position_is_nullable()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['position'] = null;

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['position' => null]);

        $this->assertDatabaseHas('teachers', ['position' => null]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_position_is_trimmed()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        $this->payload['position'] = ' ' . $this->payload['position'] . ' ';

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertCreated()
            ->assertJsonFragment(['position' => trim($this->payload['position'])]);

        $this->assertDatabaseHas('teachers', ['position' => trim($this->payload['position'])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_position_length_validation()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        // Test the maximum length validation.
        $this->payload['position'] = str_repeat('a', 129);
        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['position' => __('validation.max.string', ['attribute' => 'position', 'max' => 128])]);
    }

    /**
     * Validation test.
     *
     * @see StoreTeacherRequest::rules()
     */
    public function test_is_admin_is_required()
    {
        $this->actingAsTeacher($this->fakeAdminTeacher());

        unset($this->payload['is_admin']);

        $this->postJson(route('api.v1.teachers.store', $this->payload))
            ->assertStatus(422)
            ->assertInvalid(['is_admin' => __('validation.required', ['attribute' => 'is admin'])]);
    }
}
