<?php

namespace Tests\Feature\Classrooms;

use App\Enums\ActivityType;
use App\Http\Controllers\Api\V1\ClassroomController;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->payload = [
            'name' => fake()->name,
            'pass_grade' => fake()->numberBetween(0, 100),
            'attempts' => fake()->numberBetween(1, 10),
            'mastery_enabled' => fake()->boolean,
            'self_rating_enabled' => fake()->boolean,
        ];
    }

    /**
     * Authorization test: admin teacher.
     */
    public function test_an_admin_teacher_can_update_a_classroom_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;

        $response = $this->putJson(route('api.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the request is successful.
        $response->assertOk()->assertJsonFragment(['success' => true]);
    }

    /**
     * Authorization test: admin teacher.
     */
    public function test_an_admin_teacher_is_unauthorized_to_update_a_classroom_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school2);
        $classroom = $this->fakeClassroom($teacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    /**
     * Authorization test: non-admin teacher.
     */
    public function test_a_non_admin_teacher_can_update_a_classroom_that_they_own(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk()->assertJsonFragment(['success' => true]);
    }

    /**
     * Authorization test: non-admin teacher.
     */
    public function test_a_non_admin_teacher_is_unauthorized_to_update_classroom_that_they_do_not_own(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->putJson(route('api.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    /**
     * Validation test: field "owner_id".
     */
    public function test_it_does_not_allow_an_admin_teacher_to_update_the_classroom_owner_to_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $classroom = $this->fakeClassroom($adminTeacher);

        $school2 = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->payload['owner_id'] = $teacher->id;

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a "422" status code.
        $response->assertUnprocessable();
    }

    /**
     * Validation test: field "owner_id".
     */
    public function test_it_does_not_allow_a_non_admin_teacher_to_update_the_classroom_owner_to_another_teacher(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $this->payload['owner_id'] = $teacher->id;

        $response = $this->putJson(route('api.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response has a 422 status code.
        $response->assertUnprocessable();
    }

    /**
     * Operation test.
     */
    public function test_it_returns_the_updated_classroom()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $response = $this->putJson(route('api.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the response returns the updated classroom details.
        $responseData = $response->json()['data'];
        $this->assertEquals($classroom->id, $responseData['id']);
        $this->assertEquals($classroom->school_id, $responseData['school_id']);
        $this->assertEquals($this->payload['year_id'], $responseData['year_id']);
        $this->assertEquals($classroom->type->value, $responseData['type']);
        $this->assertEquals($this->payload['name'], $responseData['name']);
        $this->assertEquals($this->payload['pass_grade'], $responseData['pass_grade']);
        $this->assertEquals($this->payload['attempts'], $responseData['attempts']);
        $this->assertEquals($this->payload['mastery_enabled'], $responseData['mastery_enabled']);
        $this->assertEquals($this->payload['self_rating_enabled'], $responseData['self_rating_enabled']);
        $this->assertEquals($nonAdminTeacher->id, $responseData['owner']['id']);
        $this->assertEquals($nonAdminTeacher->title, $responseData['owner']['title']);
        $this->assertEquals($nonAdminTeacher->first_name, $responseData['owner']['first_name']);
    }

    /**
     * Operation test.
     */
    public function test_it_updates_the_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $this->putJson(route('api.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the classroom was updated correctly.
        $classroom->refresh();
        $this->assertEquals($this->payload['name'], $classroom->name);
        $this->assertEquals($this->payload['pass_grade'], $classroom->defaultClassroomGroup->pass_grade);
        $this->assertEquals($this->payload['attempts'], $classroom->defaultClassroomGroup->attempts);
        $this->assertEquals($this->payload['mastery_enabled'], $classroom->mastery_enabled);
        $this->assertEquals($this->payload['self_rating_enabled'], $classroom->self_rating_enabled);
        $this->assertEquals($this->payload['owner_id'], $classroom->owner_id);
        $this->assertEquals($this->payload['year_id'], $classroom->year_id);
    }

    /**
     * Operation test.
     */
    public function test_it_logs_updated_classroom_activity(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $this->assertDatabaseCount('activities', 0);

        $this->putJson(route('api.v1.classrooms.update', ['classroom' => $classroom]), $this->payload);

        // Assert that the activity was logged.
        $this->assertDatabaseCount('activities', 1);

        // Assert that the activity was logged correctly.
        $activity = Activity::first();
        $classroom->refresh();
        $this->assertEquals($adminTeacher->id, $activity->actor_id);
        $this->assertEquals(ActivityType::UPDATED_CLASSROOM, $activity->type);
        $this->assertEquals($classroom->updated_at, $activity->acted_at);
        $this->assertIsArray($activity->data);
        $this->assertEquals($classroom->id, $activity->data['id']);
        $this->assertContains($this->payload['name'], $activity->data['request']);
    }
}
