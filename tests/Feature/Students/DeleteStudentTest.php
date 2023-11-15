<?php

namespace Feature\Students;

use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Middleware\SetAuthenticationDefaults;
use App\Models\Activity;
use App\Models\Users\Student;
use App\Policies\StudentPolicy;
use Tests\TestCase;

class DeleteStudentTest extends TestCase
{
    /**
     * Authentication test.
     *
     * @see SetAuthenticationDefaults::handle()
     */
    public function test_a_guest_is_unauthenticated_to_delete_a_student()
    {
        $school = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school);

        $response = $this->deleteJson(route('api.v1.students.destroy', $student));

        // Assert that the response has a 401 “Unauthorized” status code.
        $response->assertUnauthorized();
    }

    /**
     * Authorization & Operation test.
     *
     * @see StudentPolicy::destroy()
     * @see StudentController::destroy()
     */
    public function test_an_admin_teacher_can_delete_a_student_in_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $studentsCount = Student::count();
        $activitiesCount = Activity::count();

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.v1.students.destroy', $student));

        // Assert that the response has a 204 “No Content” status code.
        $response->assertNoContent();

        // Assert that the student was soft-deleted.
        $this->assertSoftDeleted('students', ['id' => $student->id]);

        // Assert that the student was not removed from the database.
        $this->assertDatabaseCount('students', $studentsCount);

        // Assert that the activity was logged.
        $student->refresh();
        $this->assertDatabaseCount('activities', $activitiesCount + 1);
        $loggedActivity = Activity::first();
        $this->assertEquals($adminTeacher->asUser()->id, $loggedActivity->actor_id);
        $this->assertEquals('deleted student', $loggedActivity->type);
        $this->assertArrayHasKey('student_id', $loggedActivity->data);
        $this->assertEquals($student->id, $loggedActivity->data['student_id']);
        $this->assertEquals($student->deleted_at, $loggedActivity->acted_at);
    }

    /**
     * Authorization test.
     * @see StudentPolicy::destroy()
     */
    public function test_an_admin_teacher_cannot_delete_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.v1.students.destroy', $student));

        // Assert that the response has a 404 “Not Found” status code.
        $response->assertNotFound();

        // Assert that the student was not soft-deleted.
        $this->assertNotSoftDeleted('students', ['id' => $student->id]);
    }

    /**
     * Authorization test.
     * @see StudentPolicy::destroy()
     */
    public function test_a_non_admin_teacher_is_unauthorized_to_delete_a_student_in_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.v1.students.destroy', $student));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that the student was not soft-deleted.
        $this->assertNotSoftDeleted('students', ['id' => $student->id]);
    }

    /**
     * Authorization test.
     * @see StudentPolicy::destroy()
     */
    public function test_a_non_admin_teacher_is_unauthorized_to_delete_a_student_in_another_school()
    {
        $school1 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.v1.students.destroy', $student));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that the student was not soft-deleted.
        $this->assertNotSoftDeleted('students', ['id' => $student->id]);
    }
}
