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

    /**
     * The payload to use for updating the classroom.
     *
     * @var array
     */
    protected array $payload;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->payload = [
            'name' => fake()->name,
            'pass_grade' => fake()->numberBetween(0, 100),
            'attempts' => fake()->numberBetween(1, 10),
        ];
    }

    public function test_admin_teachers_can_update_classrooms_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk();

        // Assert that the response returns the updated classroom details.
        $response->assertJsonFragment([
            'id' => $classroom->id,
            'school_id' => $school->id,
            'type' => $classroom->type,
            'name' => $this->payload['name'],
            'owner_id' => $nonAdminTeacher->id,
            'pass_grade' => $this->payload['pass_grade'],
            'attempts' => $this->payload['attempts'],
        ]);

        // Asser that ClassroomUpdated event was dispatched.
        Event::assertDispatched(ClassroomUpdated::class);
    }

    public function test_admin_teachers_cannot_update_the_classroom_owner_to_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $classroom = $this->fakeClassroom($adminTeacher);

        $school2 = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->payload['owner_id'] = $teacher->id;

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a "422" status code.
        $response->assertStatus(422);
    }

    public function test_admin_teachers_are_unauthorized_to_update_classrooms_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school2);
        $classroom = $this->fakeClassroom($teacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    public function test_non_admin_teachers_can_update_classrooms_that_they_owns(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk();

        // Assert that the response returns the updated classroom details.
        $response->assertJsonFragment([
            'id' => $classroom->id,
            'school_id' => $school->id,
            'type' => $classroom->type,
            'name' => $this->payload['name'],
            'owner_id' => $nonAdminTeacher->id,
            'pass_grade' => $this->payload['pass_grade'],
            'attempts' => $this->payload['attempts'],
        ]);

        // Asser that ClassroomUpdated event was dispatched.
        Event::assertDispatched(ClassroomUpdated::class);
    }

    public function test_non_admin_teacher_cannot_update_the_owner_of_the_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $this->payload['owner_id'] = $teacher->id;

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }

    public function test_non_admin_teachers_are_unauthorized_to_update_classroom_that_they_do_not_own(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->putJson(route('api.teachers.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }
}
