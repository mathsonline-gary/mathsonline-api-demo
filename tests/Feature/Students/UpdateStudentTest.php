<?php

namespace Feature\Students;

use App\Enums\ActivityType;
use App\Models\Activity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateStudentTest extends TestCase
{
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
            'password' => 'new_password',
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'confetti_enabled' => fake()->boolean,
        ];
    }

    public function test_a_guest_is_unauthenticated_to_update_details_of_a_student()
    {
        $school = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school);

        $response = $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response has a 401 “Unauthorized” status code.
        $response->assertUnauthorized();
    }

    public function test_an_admin_teacher_can_update_details_of_a_student_in_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response is successful with updated student profile.
        $response->assertOk()
            ->assertJsonSuccess();
    }

    public function test_an_admin_teacher_is_unauthorized_to_update_details_of_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    public function test_a_non_admin_teacher_can_update_details_of_a_managed_student(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->attachStudentsToClassroomGroup($classroom->defaultClassroomGroup, [$student->id]);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonSuccess();
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_update_details_of_a_student_who_is_not_managed_by_them(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    public function test_username_is_optional()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        unset($this->payload['username']);

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['username' => $student->username]);
    }

    public function test_username_must_be_unique()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['username'] = $this->fakeStudent($school)->username;

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');
    }

    public function test_username_length_validation()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        // Test that the minimum length of a username is 3.
        $this->payload['username'] = str_repeat('a', 2);
        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');

        // Test that the maximum length of a username is 32.
        $this->payload['username'] = str_repeat('a', 33);
        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');
    }

    public function test_email_is_optional()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        unset($this->payload['email']);

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['email' => $student->email]);
    }

    public function test_email_must_be_valid_email_address()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['email'] = 'invalid-email';

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_first_name_is_optional()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        unset($this->payload['first_name']);

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['first_name' => $student->first_name]);
    }

    public function test_first_name_is_trimmed()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['first_name'] = ' ' . $this->payload['first_name'] . ' ';

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['first_name' => trim($this->payload['first_name'])]);
    }

    public function test_first_name_length_validation()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        // The minimum length of a first name is 1.
        $this->payload['first_name'] = '';
        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('first_name');

        // The maximum length of a first name is 32.
        $this->payload['first_name'] = str_repeat('a', 33);
        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('first_name');
    }

    public function test_last_name_is_optional()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        unset($this->payload['last_name']);

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['last_name' => $student->last_name]);
    }

    public function test_last_name_is_trimmed()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['last_name'] = ' ' . $this->payload['last_name'] . ' ';

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk()
            ->assertJsonFragment(['last_name' => trim($this->payload['last_name'])]);
    }

    public function test_last_name_length_validation()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        // The minimum length of a last name is 1.
        $this->payload['last_name'] = '';
        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('last_name');

        // The maximum length of a last name is 32.
        $this->payload['last_name'] = str_repeat('a', 33);
        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('last_name');
    }

    public function test_password_is_optional()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);
        $oldPassword = $student->asUser()->password;

        $this->actingAsTeacher($adminTeacher);

        unset($this->payload['password']);

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertOk();

        $student->refresh();
        $this->assertEquals($oldPassword, $student->asUser()->password);
    }

    public function test_password_length_validation(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        // Test that the min length of the password attribute is 4 characters.
        $this->payload['password'] = Str::random(2);
        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertInvalid('password');

        // Test that the max length of the password attribute is 32 characters.
        $this->payload['password'] = Str::random(33);
        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload)
            ->assertUnprocessable()
            ->assertInvalid('password');
    }

    public function test_it_responses_with_updated_student_details()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the response has the updated student details.
        $response->assertOk()
            ->assertJsonFragment([
                'username' => $this->payload['username'],
                'email' => $this->payload['email'],
                'first_name' => $this->payload['first_name'],
                'last_name' => $this->payload['last_name'],
            ]);
    }

    public function test_it_updates_the_student()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload);

        $student->refresh();

        // Assert that the student details are updated.
        $this->assertEquals($this->payload['username'], $student->username);
        $this->assertEquals($this->payload['email'], $student->email);
        $this->assertEquals($this->payload['first_name'], $student->first_name);
        $this->assertEquals($this->payload['last_name'], $student->last_name);

        // Assert that the associated user details are updated.
        $this->assertEquals($this->payload['username'], $student->asUser()->login);
        $this->assertEquals($this->payload['email'], $student->asUser()->email);
        $this->assertTrue(Hash::check($this->payload['password'], $student->asUser()->password));

        // Assert that the associated student settings are updated.
        $this->assertEquals($this->payload['confetti_enabled'], $student->settings->confetti_enabled);
    }

    public function test_it_logs_the_updated_student_activity()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $activityCount = Activity::count();

        $this->putJson(route('api.v1.students.update', ['student' => $student]), $this->payload);

        // Assert that the activity is logged.
        $this->assertDatabaseCount('activities', $activityCount + 1);

        // Assert that the activity is logged correctly.
        {
            $activity = Activity::orderByDesc('id')->first();
            $student->refresh();

            $this->assertEquals($adminTeacher->asUser()->id, $activity->actor_id);
            $this->assertEquals(ActivityType::UPDATED_STUDENT, $activity->type);
            $this->assertEquals($student->updated_at, $activity->acted_at);
            $this->assertEquals($student->id, $activity->data['id']);
            $this->assertEquals($this->payload['username'], $activity->data['payload']['username']);
            $this->assertEquals($this->payload['email'], $activity->data['payload']['email']);
            $this->assertEquals($this->payload['first_name'], $activity->data['payload']['first_name']);
            $this->assertEquals($this->payload['last_name'], $activity->data['payload']['last_name']);
            $this->assertEquals($this->payload['confetti_enabled'], $activity->data['payload']['confetti_enabled']);
            $this->assertArrayNotHasKey('password', $activity->data['payload']);
        }
    }
}
