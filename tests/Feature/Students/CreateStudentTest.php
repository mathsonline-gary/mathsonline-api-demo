<?php

namespace Tests\Feature\Students;

use App\Models\Activity;
use App\Models\Users\Student;
use Illuminate\Support\Str;
use Tests\TestCase;

class CreateStudentTest extends TestCase
{
    protected string $routeName = 'api.v1.students.store';

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
            'settings' => [
                'expired_tasks_excluded' => fake()->boolean,
                'confetti_enabled' => fake()->boolean,
            ],
        ];
    }

    public function test_a_guest_is_unauthenticated_to_create_a_student(): void
    {
        $response = $this->postJson(route($this->routeName, $this->payload));

        // Assert that the response has a 401 “Unauthorized” status code.
        $response->assertUnauthorized();
    }

    public function test_a_teacher_in_an_unsubscribed_school_cannot_create_a_student(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $teacher = $this->fakeTeacher($school);
        }

        $this->actingAsTeacher($teacher);

        $response = $this->postJson(route($this->routeName, $this->payload));

        $response->assertUnsubscribed();
    }

    public function test_an_admin_teacher_can_create_a_student_in_their_school(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route($this->routeName, $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated()
            ->assertJsonSuccessful();
    }

    public function test_an_admin_teacher_can_only_create_a_student_in_their_school()
    {
        {
            $school1 = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school1);

            $adminTeacher = $this->fakeAdminTeacher($school1);

            $school2 = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school2);

            $this->payload['school_id'] = $school2->id;
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route($this->routeName, $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated()
            ->assertJsonSuccessful();
    }

    public function test_a_non_admin_teacher_can_create_a_student_in_classroom_groups_that_they_manage(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

            // Create a classroom group of which the non-admin teacher is the owner.
            $classroom1 = $this->fakeClassroom($nonAdminTeacher);

            // Create a classroom group of which the non-admin teacher is a secondary teacher.
            $classroom2 = $this->fakeClassroom($adminTeacher);
            $classroomGroup = $this->fakeCustomClassroomGroup($classroom2);
            $classroom2->secondaryTeachers()->attach($nonAdminTeacher);

            $this->payload['classroom_group_ids'] = [
                $classroom1->defaultClassroomGroup->id,
                $classroomGroup->id,
            ];
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->postJson(route($this->routeName, $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated()
            ->assertJsonSuccessful();
    }

    public function test_a_non_admin_teacher_cannot_create_a_student_in_classrooms_that_they_do_not_manage(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

            // Create a classroom groups not managed by the non-admin teacher.
            $classroom1 = $this->fakeClassroom($adminTeacher);
            $classroom2 = $this->fakeClassroom($adminTeacher);
            $classroomGroup = $this->fakeCustomClassroomGroup($classroom2);

            $this->payload['classroom_group_ids'] = [
                $classroom1->defaultClassroomGroup->id,
                $classroomGroup->id,
            ];
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->postJson(route($this->routeName, $this->payload));

        // Assert that the request is invalid.
        $response->assertUnprocessable()
            ->assertInvalid('classroom_group_ids.0');
    }

    public function test_it_returns_the_created_student()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route($this->routeName, $this->payload));

        // Assert that the response contains the student.
        $response->assertCreated()
            ->assertJsonSuccessful()
            ->assertJsonFragment(['school_id' => $school->id])
            ->assertJsonFragment(['username' => $this->payload['username']])
            ->assertJsonFragment(['email' => $this->payload['email']])
            ->assertJsonFragment(['first_name' => $this->payload['first_name']])
            ->assertJsonFragment(['last_name' => $this->payload['last_name']])
            ->assertJsonMissing(['password']);
    }

    public function test_username_is_required(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            unset($this->payload['username']);
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username', __('validation.required', ['attribute' => 'username']));
    }

    public function test_username_must_be_string_and_trimmed(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $this->payload['username'] = '  ';
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username', __('validation.required', ['attribute' => 'username']));
    }

    public function test_username_must_be_unique(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $this->payload['username'] = $this->fakeStudent()->username;
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username', __('validation.unique', ['attribute' => 'username']));
    }

    public function test_username_length_validation(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);
        }

        $this->actingAsTeacher($adminTeacher);

        // Test that the min length of the username attribute is 3 characters.
        $this->payload['username'] = str_repeat('a', 2);
        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username', __('validation.min.string', ['attribute' => 'username', 'min' => 3]));

        // Test that the max length of the username attribute is 32 characters.
        $this->payload['username'] = str_repeat('a', 33);
        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('username', __('validation.max.string', ['attribute' => 'username', 'max' => 32]));
    }

    public function test_email_is_optional(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            unset($this->payload['email']);
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertCreated()
            ->assertJsonSuccessful()
            ->assertJsonFragment(['email' => null]);

        // Assert that the email is null in the database.
        $this->assertDatabaseHas('students', ['email' => null]);
    }

    public function test_email_is_trimmed(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $this->payload['email'] = '  test@test.com  ';
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertCreated()
            ->assertJsonSuccessful()
            ->assertJsonFragment(['email' => 'test@test.com']);

        // Assert that the email is trimmed in the database.
        $this->assertDatabaseHas('students', ['email' => 'test@test.com']);
    }

    public function test_email_must_be_valid(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $this->payload['email'] = 'test';
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('email', __('validation.email', ['attribute' => 'email']));
    }

    public function test_first_name_is_required(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            unset($this->payload['first_name']);
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('first_name', __('validation.required', ['attribute' => 'first name']));
    }

    public function test_first_name_must_be_string_and_trimmed(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $this->payload['first_name'] = '  ';
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('first_name', __('validation.required', ['attribute' => 'first name']));
    }

    public function test_first_name_length_validation(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);
        }

        $this->actingAsTeacher($adminTeacher);

        // Test that the first_name attribute must be between 1 and 32 characters.
        $this->payload['first_name'] = str_repeat('a', 33);
        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('first_name', __('validation.max.string', ['attribute' => 'first name', 'max' => 32]));
    }

    public function test_last_name_is_required(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            unset($this->payload['last_name']);
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('last_name', __('validation.required', ['attribute' => 'last name']));
    }

    public function test_last_name_must_be_string_and_trimmed(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $this->payload['last_name'] = '  ';
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('last_name', __('validation.required', ['attribute' => 'last name']));
    }

    public function test_last_name_length_validation(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $this->payload['last_name'] = str_repeat('a', 33);
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('last_name', __('validation.max.string', ['attribute' => 'last name', 'max' => 32]));
    }

    public function test_password_is_required(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            unset($this->payload['password']);
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password', __('validation.required', ['attribute' => 'password']));
    }

    public function test_password_length_validation(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);
        }

        $this->actingAsTeacher($adminTeacher);

        // Test that the min length of the password attribute is 4 characters.
        $this->payload['password'] = Str::random(3);
        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password', __('validation.min.string', ['attribute' => 'password', 'min' => 4]));

        // Test that the max length of the password attribute is 32 characters.
        $this->payload['password'] = Str::random(33);
        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('password', __('validation.max.string', ['attribute' => 'password', 'max' => 32]));
    }

    public function test_classroom_group_ids_is_required_if_the_user_is_a_non_admin_teacher()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('classroom_group_ids', __('validation.required', ['attribute' => 'classroom_group_ids']));
    }

    public function test_classroom_groups_should_be_in_the_same_school_as_the_teacher(): void
    {
        {
            $school1 = $this->fakeTraditionalSchool();
            $school2 = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school1);
            $this->fakeSubscription($school2);

            $adminTeacher1 = $this->fakeAdminTeacher($school1);
            $adminTeacher2 = $this->fakeAdminTeacher($school2);

            $classroom1 = $this->fakeClassroom($adminTeacher2);
            $classroom2 = $this->fakeClassroom($adminTeacher2);

            $this->payload['classroom_group_ids'] = [
                $classroom1->defaultClassroomGroup->id,
                $classroom2->defaultClassroomGroup->id,
            ];
        }

        $this->actingAsTeacher($adminTeacher1);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('classroom_group_ids.0');
    }

    public function test_classroom_groups_should_be_managed_by_the_non_admin_teacher(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

            $classroom1 = $this->fakeClassroom($adminTeacher);
            $classroom2 = $this->fakeClassroom($adminTeacher);

            $this->payload['classroom_group_ids'] = [
                $classroom1->defaultClassroomGroup->id,
                $classroom2->defaultClassroomGroup->id,
            ];
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('classroom_group_ids.0');
    }

    public function test_classroom_groups_should_be_from_different_classrooms(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $classroom = $this->fakeClassroom($adminTeacher);
            $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom);

            $this->payload['classroom_group_ids'] = [
                $classroom->defaultClassroomGroup->id,
                $customClassroomGroup->id,
            ];
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertUnprocessable()
            ->assertInvalid('classroom_group_ids.1');
    }

    public function test_it_logs_student_created_activity()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $activityCount = Activity::count();
        }

        $this->actingAsTeacher($adminTeacher);

        $this->postJson(route($this->routeName, $this->payload))
            ->assertCreated()
            ->assertJsonSuccessful();

        // Assert that the student created activity is created in the database.
        $this->assertDatabaseCount('activities', $activityCount + 1);

        // Assert that the student created activity is created correctly in the database.
        $activity = Activity::latest('acted_at')->first();
        $student = Student::latest()->first();
        $this->assertEquals($adminTeacher->asUser()->id, $activity->actor_id);
        $this->assertEquals(Activity::TYPE_CREATE_STUDENT, $activity->type);
        $this->assertArrayHasKey('id', $activity->data);
        $this->assertEquals($student->id, $activity->data['id']);
        $this->assertEquals($student->created_at, $activity->acted_at);
    }

    public function test_it_assigns_the_student_into_the_classroom_groups()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $classroom1 = $this->fakeClassroom($adminTeacher);
            $classroom2 = $this->fakeClassroom($adminTeacher);

            $classroomGroup1 = $this->fakeCustomClassroomGroup($classroom1);
            $classroomGroup2 = $this->fakeCustomClassroomGroup($classroom2);
        }

        $this->actingAsTeacher($adminTeacher);

        $this->payload['classroom_group_ids'] = [
            $classroomGroup1->id,
            $classroomGroup2->id,
        ];

        $this->postJson(route($this->routeName, $this->payload))
            ->assertCreated()
            ->assertJsonSuccessful();

        // Assert that the student is assigned into the classroom groups.
        $student = Student::latest()->first();

        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $classroomGroup1->id,
        ]);

        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $classroomGroup2->id,
        ]);
    }
}
