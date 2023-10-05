<?php

namespace Feature\Teachers;

use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Middleware\SetAuthenticationDefaults;
use App\Models\Activity;
use App\Policies\TeacherPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTeacherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authentication test.
     *
     * @see SetAuthenticationDefaults::handle()
     */
    public function test_a_guest_is_unauthenticated_to_delete_a_teacher(): void
    {
        $teacher = $this->fakeNonAdminTeacher();

        $response = $this->deleteJson(route('api.v1.teachers.destroy', $teacher));

        // Assert that the request is unauthenticated.
        $response->assertUnauthorized();
    }

    /**
     * Authorization & Operation test.
     *
     * @see TeacherPolicy::delete()
     * @see TeacherController::destroy()
     */
    public function test_an_admin_teacher_can_delete_a_teacher_in_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.v1.teachers.destroy', $teacher));

        // Assert that the response returns no content.
        $response->assertNoContent();

        // Assert that $teacher is soft-deleted.
        $this->assertSoftDeleted('teachers', ['id' => $teacher->id]);
        $this->assertSoftDeleted('users', ['id' => $teacher->asUser()->id]);
    }

    /**
     * Operation test.
     *
     * @see TeacherPolicy::delete()
     */
    public function test_it_logs_deleted_teacher_activity(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $activityCount = Activity::count();

        $this->actingAsTeacher($adminTeacher);

        $this->deleteJson(route('api.v1.teachers.destroy', $teacher));

        // Assert that the activity is logged correctly.
        $this->assertDatabaseCount('activities', $activityCount + 1);
        $activity = Activity::latest('id')->first();
        $teacher->refresh();
        $this->assertEquals($adminTeacher->asUser()->id, $activity->actor_id);
        $this->assertEquals('deleted teacher', $activity->type);
        $this->assertEquals($teacher->deleted_at, $activity->acted_at);
        $this->assertArrayHasKey('teacher_id', $activity->data);
        $this->assertEquals($teacher->id, $activity->data['teacher_id']);
    }

    /**
     * Operation test.
     *
     * @see TeacherPolicy::delete()
     */
    public function test_it_detaches_classrooms_from_the_teacher(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        // Set the $teacher as the owner of $classroom1, and the secondary teacher of $classroom2.
        $classroom1 = $this->fakeClassroom($adminTeacher);
        $classroom2 = $this->fakeClassroom($teacher);
        $this->attachSecondaryTeachersToClassroom($classroom1, [$teacher->id]);

        // Assert $teacher was set correctly
        $this->assertDatabaseHas('classrooms', ['id' => $classroom2->id, 'owner_id' => $teacher->id])
            ->assertDatabaseHas('classroom_secondary_teacher', [
                'classroom_id' => $classroom1->id,
                'teacher_id' => $teacher->id,
            ]);

        $this->actingAsTeacher($adminTeacher);

        $this->deleteJson(route('api.v1.teachers.destroy', $teacher));

        // Assert that $teacher is removed as the owner of $classroom2, and as a secondary teacher of $classroom1.
        $this->assertDatabaseHas('classrooms', ['id' => $classroom2->id, 'owner_id' => null])
            ->assertDatabaseMissing('classrooms', ['owner_id' => $teacher->id])
            ->assertDatabaseMissing('classroom_secondary_teacher', [
                'classroom_id' => $classroom1->id,
                'teacher_id' => $teacher->id,
            ]);
    }

    /**
     * Authorization test.
     *
     * @see TeacherPolicy::delete()
     */
    public function test_an_admin_teacher_is_unauthorised_to_delete_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $teacherAdmin = $this->fakeAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        // Assert that $teacher is in the database.
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->deleteJson(route('api.v1.teachers.destroy', $teacher));

        // Assert that the request is unauthorised.
        $response->assertForbidden();

        // Assert that $teacher is not soft-deleted.
        $this->assertNotSoftDeleted('teachers', ['id' => $teacher->id]);
    }

    /**
     * Authorization test.
     *
     * @see TeacherPolicy::delete()
     */
    public function test_a_non_admin_teacher_is_unauthorised_to_delete_a_teacher_in_their_school()
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.v1.teachers.destroy', $teacher));

        // Assert that the request is unauthorised.
        $response->assertForbidden();

        // Assert that $teacher is not soft-deleted.
        $this->assertNotSoftDeleted('teachers', ['id' => $teacher->id]);
    }

    /**
     * Authorization test.
     *
     * @see TeacherPolicy::delete()
     */
    public function test_a_non_admin_teacher_is_unauthorised_to_delete_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.v1.teachers.destroy', $teacher));

        // Assert that the request is unauthorised
        $response->assertForbidden();

        // Assert that $teacher is not deleted
        $this->assertNotSoftDeleted('teachers', ['id' => $teacher->id]);
    }
}