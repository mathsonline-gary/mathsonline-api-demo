<?php

namespace Feature\Classrooms;

use App\Enums\ActivityType;
use App\Http\Controllers\Api\V1\ClassroomController;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see ClassroomController::destroy()
 */
class DeleteClassroomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Authorization test.
     */
    public function test_a_guest_cannot_delete_a_classroom()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $classroom = $this->fakeClassroom($adminTeacher);

        $this->assertGuest();

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has a 401 “Unauthorized” status code.
        $response->assertUnauthorized();
    }

    /**
     * Authorization test.
     */
    public function test_an_admin_teacher_can_delete_a_classroom_from_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has 200 status code.
        $response->assertOk()
            ->assertJsonFragment(['message' => 'The classroom was deleted successfully.']);
    }

    /**
     * Authorization test.
     */
    public function test_an_admin_teacher_is_unauthorised_to_delete_a_classroom_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher1 = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $adminTeacher2 = $this->fakeAdminTeacher($school2);
        $classroom = $this->fakeClassroom($adminTeacher2);

        $this->actingAsTeacher($adminTeacher1);

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

    }

    /**
     * Authorization test.
     */
    public function test_a_non_admin_teacher_can_delete_a_classroom_owned_by_them(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        $response->assertOk()
            ->assertJsonFragment(['message' => 'The classroom was deleted successfully.']);
    }

    /**
     * Authorization test.
     */
    public function test_a_non_admin_teacher_is_unauthorised_to_delete_a_classroom_that_is_not_owned_by_them(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    /**
     * Operation test.
     */
    public function test_it_soft_deletes_the_classroom()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the classroom was soft-deleted.
        $this->assertSoftDeleted('classrooms', ['id' => $classroom->id]);

        // Assert that the classroom groups were soft-deleted.
        $this->assertSoftDeleted('classroom_groups', ['classroom_id' => $classroom->id]);
    }

    /**
     * Operation test.
     */
    public function test_it_logs_deleted_classroom_activity()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $classroom = $this->fakeClassroom($adminTeacher);

        $this->assertDatabaseCount('activities', 0);

        $this->actingAsTeacher($adminTeacher);

        $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the activity was logged.
        $this->assertDatabaseCount('activities', 1);

        // Assert that the activity was logged correctly.
        $classroom->refresh();
        $activity = Activity::first();
        $this->assertEquals($adminTeacher->id, $activity->actor_id);
        $this->assertEquals(ActivityType::DELETED_CLASSROOM, $activity->type);
        $this->assertEquals($classroom->deleted_at, $activity->acted_at);
        $this->assertEquals($classroom->id, $activity->data['id']);
    }
}
