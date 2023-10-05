<?php

namespace Feature\Teachers;

use App\Enums\ActivityTypes;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Models\Activity;
use App\Policies\TeacherPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateTeacherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The payload used to update the teacher.
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
            'password' => 'new-password',
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'position' => fake()->word,
            'title' => 'Mr',
        ];
    }

    /**
     * Authentication test.
     */
    public function test_a_guest_cannot_update_a_teacher(): void
    {
        $teacher = $this->fakeNonAdminTeacher();

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        // Assert that the request is unauthenticated.
        $response->assertUnauthorized();
    }

    /**
     * Authorization test.
     *
     * @see TeacherPolicy::update()
     */
    public function test_an_admin_teacher_can_update_personal_details(): void
    {
        $teacher = $this->fakeAdminTeacher();

        $this->actingAsTeacher($teacher);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();
    }

    /**
     * Authorization test.
     *
     * @see TeacherPolicy::update()
     */
    public function test_a_non_admin_teacher_can_update_personal_details(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();
    }

    /**
     * Authorization test.
     *
     * @see TeacherPolicy::update()
     */
    public function test_an_admin_teacher_can_update_the_details_of_a_teacher_in_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['is_admin'] = true;

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        // Assert that the response is successful with updated teacher profile.
        $response->assertOk();
    }

    /**
     * Authorization test.
     *
     * @see TeacherPolicy::update()
     */
    public function test_non_admin_teachers_are_unauthorised_to_update_other_teachers_in_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertForbidden();
    }

    /**
     * Authorization test.
     *
     * @see TeacherPolicy::update()
     */
    public function test_an_admin_teacher_is_unauthorised_to_update_the_details_of_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertForbidden();
    }

    /**
     * Authorization test.
     *
     * @see TeacherPolicy::update()
     */
    public function test_a_non_admin_teacher_is_unauthorised_to_update_the_details_of_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertForbidden();

        // Assert that the teacher was unchanged.
        $originalAttributes = $teacher->getAttributes();
        $teacher->refresh();
        foreach ($originalAttributes as $attribute => $value) {
            $this->assertEquals($value, $teacher->$attribute);
        }
    }

    /**
     * Authorization test.
     */
    public function test_an_admin_teacher_cannot_update_a_soft_deleted_teacher_in_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school, 1, ['deleted_at' => now()]);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertNotFound();
    }

    /**
     * Operation test.
     *
     * @see TeacherController::update()
     */
    public function test_it_returns_the_id_of_the_updated_teacher()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $teacher->id,
            ]);
    }

    /**
     * Operation test.
     *
     * @see TeacherController::update()
     */
    public function test_it_updates_the_details_of_the_teacher(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['is_admin'] = true;
        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the teacher was updated correctly.
        $teacher->refresh();
        $this->assertEquals($this->payload['username'], $teacher->username);
        $this->assertEquals($this->payload['email'], $teacher->email);
        $this->assertEquals($this->payload['first_name'], $teacher->first_name);
        $this->assertEquals($this->payload['last_name'], $teacher->last_name);
        $this->assertEquals($this->payload['position'], $teacher->position);
        $this->assertEquals($this->payload['title'], $teacher->title);
        $this->assertEquals($this->payload['is_admin'], $teacher->is_admin);
        $this->assertEquals($this->payload['username'], $teacher->asUser()->login);
        $this->assertTrue(Hash::check($this->payload['password'], $teacher->asUser()->password));
    }

    /**
     * Operation test.
     *
     * @see TeacherController::update()
     */
    public function test_it_prevent_admin_teachers_from_change_their_own_admin_permission(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $this->payload['is_admin'] = false;

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the "is_admin" attribute was not updated.
        $response->assertJsonFragment(['is_admin' => true]);

        // Assert that the "is_admin" attribute was not updated in the database.
        $teacher->refresh();
        $this->assertTrue($teacher->is_admin);
    }

    /**
     * Operation test.
     *
     * @see TeacherController::update()
     */
    public function test_it_prevent_a_non_admin_teacher_updating_the_is_admin_attribute(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $this->payload['is_admin'] = true;

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the "is_admin" attribute was not updated.
        $response->assertJsonFragment(['is_admin' => false]);

        // Assert that the "is_admin" attribute was not updated in the database.
        $teacher->refresh();
        $this->assertFalse($teacher->is_admin);
    }

    /**
     * Operation test.
     */
    public function test_it_logs_updated_teacher_activity(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        // Assert that no activity was logged.
        $this->assertDatabaseCount('activities', 0);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        $teacher->refresh();

        // Assert that the activity was logged.
        $this->assertDatabaseCount('activities', 1);

        // Assert that the activity was logged correctly.
        $activity = Activity::first();
        $this->assertEquals($adminTeacher->id, $activity->actor_id);
        $this->assertEquals(ActivityTypes::UPDATED_TEACHER->value, $activity->type);
        $this->assertEquals($teacher->updated_at, $activity->acted_at);
        $this->assertArrayHasKey('before', $activity->data);
        $this->assertArrayHasKey('after', $activity->data);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_username_field_is_optional(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        unset($this->payload['username']);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_username_field_must_be_unique_regardless_the_current_teacher(): void
    {
        $teacher1 = $this->fakeAdminTeacher();
        $teacher2 = $this->fakeAdminTeacher();

        $this->actingAsTeacher($teacher1);

        // Test that the username must be unique.
        $this->payload['username'] = $teacher2->username;

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher1]), $this->payload)
            ->assertInvalid(['username' => __('validation.unique', ['attribute' => 'username'])]);

        // Test it ignores the current teacher.
        $this->payload['username'] = $teacher1->username;

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher1]), $this->payload)
            ->assertOk();
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_username_field_is_trimmed(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $this->payload['username'] = ' ' . $this->payload['username'] . ' ';

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertOk();

        $teacher->refresh();
        $this->assertEquals(trim($this->payload['username']), $teacher->username);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_username_field_length_validation(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        // Test that the username must be at least 3 characters long.
        $this->payload['username'] = str_repeat('a', 2);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['username' => __('validation.min.string', ['attribute' => 'username', 'min' => 3])]);

        // Test that the username may be up to 32 characters long.
        $this->payload['username'] = str_repeat('a', 33);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['username' => __('validation.max.string', ['attribute' => 'username', 'max' => 32])]);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_email_field_is_optional(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $email = $teacher->email;

        unset($this->payload['email']);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the email was not updated.
        $teacher->refresh();
        $this->assertEquals($email, $teacher->email);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_email_field_is_nullable(): void
    {
        $teacher = $this->fakeAdminTeacher();

        $this->assertNotNull($teacher->email);

        $this->actingAsTeacher($teacher);

        $this->payload['email'] = null;

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the email was updated to null.
        $teacher->refresh();
        $this->assertNull($teacher->email);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_email_field_is_trimmed(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $this->payload['email'] = ' ' . $this->payload['email'] . ' ';

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertOk();

        $teacher->refresh();
        $this->assertEquals(trim($this->payload['email']), $teacher->email);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_email_field_length_validation(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        // Test that the email must be at least 4 characters long.
        $this->payload['email'] = str_repeat('a', 3);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['email' => __('validation.min.string', ['attribute' => 'email', 'min' => 4])]);

        // Test that the email may be up to 128 characters long.
        $this->payload['email'] = str_repeat('a', 129);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['email' => __('validation.max.string', ['attribute' => 'email', 'max' => 128])]);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_email_field_must_be_a_valid_email_address(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        // Test that the email must be a valid email address.
        $this->payload['email'] = 'invalid-email';

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['email' => __('validation.email', ['attribute' => 'email'])]);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_password_field_is_optional(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $password = $teacher->asUser()->password;

        unset($this->payload['password']);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertOk();

        // Assert that the password was not updated.
        $teacher->refresh();
        $this->assertEquals($password, $teacher->asUser()->password);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_password_field_length_validation(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        // Test that the password must be at least 4 characters long.
        $this->payload['password'] = str_repeat('a', 3);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['password' => __('validation.min.string', ['attribute' => 'password', 'min' => 4])]);

        // Test that the password may be up to 32 characters long.
        $this->payload['password'] = str_repeat('a', 33);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['password' => __('validation.max.string', ['attribute' => 'password', 'max' => 32])]);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_first_name_field_is_optional(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $firstName = $teacher->first_name;

        unset($this->payload['first_name']);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the first_name was not updated.
        $teacher->refresh();
        $this->assertEquals($firstName, $teacher->first_name);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_first_name_field_is_trimmed(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $this->payload['first_name'] = ' ' . $this->payload['first_name'] . ' ';

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertOk();

        $teacher->refresh();
        $this->assertEquals(trim($this->payload['first_name']), $teacher->first_name);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_first_name_field_length_validation(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        // Test that the first_name may be up to 255 characters long.
        $this->payload['first_name'] = str_repeat('a', 33);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['first_name' => __('validation.max.string', ['attribute' => 'first name', 'max' => 32])]);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_last_name_field_is_optional(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $lastName = $teacher->last_name;

        unset($this->payload['last_name']);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the last_name was not updated.
        $teacher->refresh();
        $this->assertEquals($lastName, $teacher->last_name);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_last_name_field_is_trimmed(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $this->payload['last_name'] = ' ' . $this->payload['last_name'] . ' ';

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertOk();

        $teacher->refresh();
        $this->assertEquals(trim($this->payload['last_name']), $teacher->last_name);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_last_name_field_length_validation(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        // Test that the last_name may be up to 255 characters long.
        $this->payload['last_name'] = str_repeat('a', 33);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['last_name' => __('validation.max.string', ['attribute' => 'last name', 'max' => 32])]);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_title_is_optional(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $title = $teacher->title;

        unset($this->payload['title']);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the title was not updated.
        $teacher->refresh();
        $this->assertEquals($title, $teacher->title);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_title_field_is_nullable(): void
    {
        $teacher = $this->fakeAdminTeacher();

        $this->assertNotNull($teacher->title);

        $this->actingAsTeacher($teacher);

        $this->payload['title'] = null;

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the title was updated to null.
        $teacher->refresh();
        $this->assertNull($teacher->title);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_title_field_is_trimmed(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $this->payload['title'] = ' ' . $this->payload['title'] . ' ';

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertOk();

        $teacher->refresh();
        $this->assertEquals(trim($this->payload['title']), $teacher->title);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_title_field_length_validation(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        // Test that the title may be up to 16 characters long.
        $this->payload['title'] = str_repeat('a', 17);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['title' => __('validation.max.string', ['attribute' => 'title', 'max' => 16])]);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_position_field_is_optional(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $position = $teacher->position;

        unset($this->payload['position']);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the position was not updated.
        $teacher->refresh();
        $this->assertEquals($position, $teacher->position);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_position_field_is_nullable(): void
    {
        $teacher = $this->fakeAdminTeacher();

        $this->assertNotNull($teacher->position);

        $this->actingAsTeacher($teacher);

        $this->payload['position'] = null;

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the position was updated to null.
        $teacher->refresh();
        $this->assertNull($teacher->position);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_position_field_is_trimmed(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $this->payload['position'] = ' ' . $this->payload['position'] . ' ';

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertOk();

        $teacher->refresh();
        $this->assertEquals(trim($this->payload['position']), $teacher->position);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_position_field_length_validation(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        // Test that the position may be up to 128 characters long.
        $this->payload['position'] = str_repeat('a', 129);

        $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload)
            ->assertInvalid(['position' => __('validation.max.string', ['attribute' => 'position', 'max' => 128])]);
    }

    /**
     * Validation test.
     *
     * @see TeacherController::update()
     */
    public function test_is_admin_field_is_optional(): void
    {
        $teacher = $this->fakeAdminTeacher();
        $this->actingAsTeacher($teacher);

        $isAdmin = $teacher->is_admin;

        unset($this->payload['is_admin']);

        $response = $this->putJson(route('api.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the is_admin was not updated.
        $teacher->refresh();
        $this->assertEquals($isAdmin, $teacher->is_admin);
    }
}
