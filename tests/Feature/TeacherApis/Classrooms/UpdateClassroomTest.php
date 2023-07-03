<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use App\Events\Classrooms\ClassroomUpdated;
use App\Http\Controllers\Api\Teachers\V1\ClassroomController;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @see ClassroomController::update()
 */
class UpdateClassroomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function test_admin_teachers_can_update_classrooms_in_the_same_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $adminTeacher = $this->createAdminTeacher($school);
        $nonAdminTeacher = $this->createNonAdminTeacher($school);

        $classroom = $this->createClassroom($adminTeacher, 1, [
            'name' => 'Old class name',
            'pass_grade' => 80,
            'attempts' => 2,
        ]);

        $this->actingAsTeacher($adminTeacher);

        $payload = [
            'name' => 'New class name',
            'owner_id' => $nonAdminTeacher->id,
            'pass_grade' => 60,
            'attempts' => 1,
        ];

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $payload);

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk();

        // Assert that the response returns the updated classroom details.
        $response->assertJsonFragment([
            'id' => $classroom->id,
            'school_id' => $school->id,
            'type' => $classroom->type,
            'name' => $payload['name'],
            'owner_id' => $nonAdminTeacher->id,
            'pass_grade' => $payload['pass_grade'],
            'attempts' => $payload['attempts'],
        ]);

        // Asser that ClassroomUpdated event was dispatched.
        Event::assertDispatched(ClassroomUpdated::class);
    }

    public function test_admin_teachers_cannot_update_the_classroom_owner_to_a_teacher_in_another_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->createTraditionalSchool();
        $adminTeacher = $this->createAdminTeacher($school1);
        $classroom = $this->createClassroom($adminTeacher);

        $school2 = $this->createTraditionalSchool();
        $teacher = $this->createNonAdminTeacher($school2);

        $payload = ['owner_id' => $teacher->id];

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $payload);

        // Assert that the response has a "422" status code.
        $response->assertStatus(422);
    }

    public function test_admin_teachers_are_unauthorized_to_update_classrooms_in_another_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->createTraditionalSchool();
        $adminTeacher = $this->createAdminTeacher($school1);

        $school2 = $this->createTraditionalSchool();
        $teacher = $this->createNonAdminTeacher($school2);
        $classroom = $this->createClassroom($teacher, 1, [
            'name' => 'Old class name',
            'pass_grade' => 80,
            'attempts' => 2,
        ]);

        $this->actingAsTeacher($adminTeacher);

        $payload = [
            'name' => 'New class name',
            'pass_grade' => 60,
            'attempts' => 1,
        ];

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    public function test_non_admin_teachers_can_update_classrooms_that_they_owns(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $nonAdminTeacher = $this->createNonAdminTeacher($school);

        $classroom = $this->createClassroom($nonAdminTeacher, 1, [
            'name' => 'Old class name',
            'pass_grade' => 80,
            'attempts' => 2,
        ]);

        $this->actingAsTeacher($nonAdminTeacher);

        $payload = [
            'name' => 'New class name',
            'pass_grade' => 60,
            'attempts' => 1,
        ];

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $payload);

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk();

        // Assert that the response returns the updated classroom details.
        $response->assertJsonFragment([
            'id' => $classroom->id,
            'school_id' => $school->id,
            'type' => $classroom->type,
            'name' => $payload['name'],
            'owner_id' => $nonAdminTeacher->id,
            'pass_grade' => $payload['pass_grade'],
            'attempts' => $payload['attempts'],
        ]);

        // Asser that ClassroomUpdated event was dispatched.
        Event::assertDispatched(ClassroomUpdated::class);
    }

    public function test_non_admin_teacher_cannot_update_the_owner_of_the_classroom(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $nonAdminTeacher = $this->createNonAdminTeacher($school);
        $teacher = $this->createNonAdminTeacher($school);

        $classroom = $this->createClassroom($nonAdminTeacher, 1, [
            'name' => 'Old class name',
            'pass_grade' => 80,
            'attempts' => 2,
        ]);

        $this->actingAsTeacher($nonAdminTeacher);

        $payload = ['owner_id' => $teacher->id];

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $payload);

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }

    public function test_non_admin_teachers_are_unauthorized_to_update_classroom_that_they_do_not_own(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $nonAdminTeacher1 = $this->createNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->createNonAdminTeacher($school);

        $classroom = $this->createClassroom($nonAdminTeacher2, 1, [
            'name' => 'Old class name',
            'pass_grade' => 80,
            'attempts' => 2,
        ]);

        $this->actingAsTeacher($nonAdminTeacher1);

        $payload = [
            'name' => 'New class name',
            'pass_grade' => 60,
            'attempts' => 1,
        ];

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }
}
