<?php

namespace Tests\Feature\Classrooms;

use App\Models\Activity;
use Tests\TestCase;

class DeleteClassroomTest extends TestCase
{
    public function test_a_guest_cannot_delete_a_classroom()
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);
        $adminTeacher = $this->fakeAdminTeacher($school);
        $classroom = $this->fakeClassroom($adminTeacher);
        $defaultClassroomGroup = $classroom->defaultClassroomGroup;

        $this->assertGuest();

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has a 401 “Unauthorized” status code.
        $response->assertUnauthorized();

        // Assert that the classroom was not deleted.
        $this->assertNotSoftDeleted($classroom);

        // Assert that the default classroom group was not deleted.
        $this->assertNotSoftDeleted($defaultClassroomGroup);

        // Assert that no activity was logged.
        $this->assertDatabaseCount(Activity::class, 0);
    }

    public function test_a_teacher_in_the_unsubscribed_school_cannot_delete_a_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->actingAsTeacher($this->fakeTeacher($school));
        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher);
        $defaultClassroomGroup = $classroom->defaultClassroomGroup;

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has unsubscription error.
        $response->assertUnsubscribed();

        // Assert that the classroom was not deleted.
        $this->assertNotSoftDeleted($classroom);

        // Assert that the default classroom group was not deleted.
        $this->assertNotSoftDeleted($defaultClassroomGroup);

        // Assert that no activity was logged.
        $this->assertDatabaseCount(Activity::class, 0);
    }

    public function test_an_admin_teacher_can_delete_a_classroom_from_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);
        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher);
        $defaultClassroomGroup = $classroom->defaultClassroomGroup;

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has 200 status code.
        $response->assertOk()->assertJsonSuccessful();

        // Assert that the classroom was deleted.
        $this->assertSoftDeleted($classroom);

        // Assert that the default classroom group was deleted.
        $this->assertSoftDeleted($defaultClassroomGroup);

        // Assert that the activity was logged.
        $this->assertDatabaseCount(Activity::class, 1);
        $this->assertDatabaseHas(Activity::class, [
            'actor_id' => $adminTeacher->asUser()->id,
            'type' => Activity::TYPE_DELETE_CLASSROOM,
        ]);
    }

    public function test_an_admin_teacher_is_unauthorized_to_delete_a_classroom_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school1);
        $adminTeacher1 = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school2);
        $adminTeacher2 = $this->fakeAdminTeacher($school2);
        $classroom = $this->fakeClassroom($adminTeacher2);
        $defaultClassroomGroup = $classroom->defaultClassroomGroup;

        $this->actingAsTeacher($adminTeacher1);

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that the classroom was not deleted.
        $this->assertNotSoftDeleted($classroom);

        // Assert that the default classroom group was not deleted.
        $this->assertNotSoftDeleted($defaultClassroomGroup);

        // Assert that no activity was logged.
        $this->assertDatabaseCount(Activity::class, 0);
    }

    public function test_a_non_admin_teacher_can_delete_a_classroom_owned_by_them(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher);
        $defaultClassroomGroup = $classroom->defaultClassroomGroup;

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        $response->assertOk()->assertJsonSuccessful();

        // Assert that the classroom was deleted.
        $this->assertSoftDeleted($classroom);

        // Assert that the default classroom group was deleted.
        $this->assertSoftDeleted($defaultClassroomGroup);

        // Assert that the activity was logged.
        $this->assertDatabaseCount(Activity::class, 1);
        $this->assertDatabaseHas(Activity::class, [
            'actor_id' => $nonAdminTeacher->asUser()->id,
            'type' => Activity::TYPE_DELETE_CLASSROOM,
        ]);
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_delete_a_classroom_that_is_not_owned_by_them(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);

        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher2);
        $defaultClassroomGroup = $classroom->defaultClassroomGroup;

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->deleteJson(route('api.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that the classroom was not deleted.
        $this->assertNotSoftDeleted($classroom);

        // Assert that the default classroom group was not deleted.
        $this->assertNotSoftDeleted($defaultClassroomGroup);

        // Assert that no activity was logged.
        $this->assertDatabaseCount(Activity::class, 0);
    }

}
