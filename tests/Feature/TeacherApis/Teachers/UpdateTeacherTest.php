<?php

namespace Tests\Feature\TeacherApis\Teachers;

use App\Events\Teachers\TeacherUpdated;
use App\Http\Controllers\Api\Teachers\V1\TeacherController;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @see TeacherController::update()
 */
class UpdateTeacherTest extends TestCase
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

        Event::fake();

        $this->payload = [
            'username' => fake()->userName,
            'email' => fake()->safeEmail,
            'password' => 'password',
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'position' => fake()->jobTitle,
            'title' => 'Mr',
        ];
    }

    /**
     * @see TeacherController::update()
     */
    public function test_teacher_admins_can_update_personal_profile(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        // Assert that the response is successful with updated teacher profile.
        $response->assertOk()
            ->assertJsonFragment(Arr::except($this->payload, 'password'))
            ->assertJsonMissingPath('password');

        // Assert that the TeacherUpdated event was dispatched.
        Event::assertDispatched(TeacherUpdated::class);
    }

    /**
     * @see TeacherController::update()
     */
    public function test_non_admin_teachers_can_update_personal_profile(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        // Assert that the response is successful with updated teacher profile.
        $response->assertOk()
            ->assertJsonFragment(Arr::except($this->payload, 'password'))
            ->assertJsonMissingPath('password');

        // Assert that the TeacherUpdated event was dispatched.
        Event::assertDispatched(TeacherUpdated::class);
    }

    /**
     * @see TeacherController::update()
     */
    public function test_non_admin_teachers_cannot_update_is_admin_attribute(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $this->payload['is_admin'] = true;

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the "is_admin" attribute was not updated.
        $response->assertJsonFragment(['is_admin' => false]);
    }

    /**
     * @see TeacherController::update()
     */
    public function test_teacher_admins_can_update_teachers_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        // Assert that the response is successful with updated teacher profile.
        $response->assertOk()
            ->assertJsonFragment(Arr::except($this->payload, 'password'))
            ->assertJsonMissingPath('password');

        // Assert that the TeacherUpdated event was dispatched.
        Event::assertDispatched(TeacherUpdated::class);
    }

    /**
     * @see TeacherController::update()
     */
    public function test_non_admin_teachers_are_unauthorised_to_update_other_teachers_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertForbidden();
    }

    /**
     * @see TeacherController::update()
     */
    public function test_teacher_admins_are_unauthorised_to_update_teachers_in_other_schools(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertForbidden();
    }

    /**
     * @see TeacherController::update()
     */
    public function test_non_admin_teachers_are_unauthorised_to_update_teachers_in_other_schools(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertForbidden();
    }
}
