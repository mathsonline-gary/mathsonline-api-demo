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

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    /**
     * @see TeacherController::update()
     */
    public function test_teacher_admins_can_update_personal_profile(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $payload = [
            'username' => 'john_doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'title' => 'Mr',
            'position' => 'Maths Teacher',
            'is_admin' => false,
        ];

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $payload);

        // Assert that the response is successful with updated teacher profile.
        $response->assertSuccessful()
            ->assertJsonFragment(Arr::except($payload, 'password'))
            ->assertJsonMissingPath('password');

        // Assert that the TeacherUpdated event was dispatched.
        Event::assertDispatched(TeacherUpdated::class);
    }

    /**
     * @see TeacherController::update()
     */
    public function test_non_admin_teachers_can_update_personal_profile(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $payload = [
            'username' => 'john_doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'title' => 'Mr',
            'position' => 'Maths Teacher',
        ];

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $payload);

        $response->assertSuccessful();
        $response->assertJsonFragment(Arr::except($payload, 'password'));
        $response->assertJsonMissingPath('password');
    }

    /**
     * @see TeacherController::update()
     */
    public function test_non_admin_teachers_cannot_update_is_admin_attribute(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $payload = [
            'is_admin' => true,
        ];

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $payload);

        $response->assertSuccessful();
        $response->assertJsonFragment(['is_admin' => false]);
    }

    /**
     * @see TeacherController::update()
     */
    public function test_teacher_admins_can_update_teachers_in_the_same_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $payload = [
            'username' => 'john_doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'title' => 'Mr',
            'position' => 'Maths Teacher',
            'is_admin' => false,
        ];

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $payload);

        $response->assertSuccessful();
        $response->assertJsonFragment(Arr::except($payload, 'password'));
        $response->assertJsonMissingPath('password');

        // Assert that the TeacherUpdated event was dispatched.
        Event::assertDispatched(TeacherUpdated::class);
    }

    /**
     * @see TeacherController::update()
     */
    public function test_non_admin_teachers_are_unauthorised_to_update_other_teachers_in_the_same_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $payload = [];

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $payload);

        $response->assertForbidden();
    }

    /**
     * @see TeacherController::update()
     */
    public function test_teacher_admins_are_unauthorised_to_update_teachers_in_other_schools(): void
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $payload = [];

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $payload);

        $response->assertForbidden();
    }

    /**
     * @see TeacherController::update()
     */
    public function test_non_admin_teachers_are_unauthorised_to_update_teachers_in_other_schools(): void
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $payload = [];

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $payload);

        $response->assertForbidden();
    }
}
