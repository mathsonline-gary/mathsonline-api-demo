<?php

namespace Tests\Feature\TeacherApis\Students;

use App\Http\Controllers\Api\Teachers\V1\StudentController;
use App\Models\Activity;
use App\Models\Users\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteStudentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @see StudentController::destroy()
     */
    public function test_an_admin_teacher_can_delete_a_student_in_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.students.destroy', $student));

        // Assert that the response has a 204 “No Content” status code.
        $response->assertNoContent();

        // Assert that the student was soft-deleted.
        $this->assertSoftDeleted('students', ['id' => $student->id,]);

        // Assert that the activity was logged.
        $student->refresh();
        $this->assertDatabaseCount('activities', 1);
        $loggedActivity = Activity::first();
        $this->assertEquals(Teacher::class, $loggedActivity->actable_type);
        $this->assertEquals($adminTeacher->id, $loggedActivity->actable_id);
        $this->assertArrayHasKey('student_id', $loggedActivity->data);
        $this->assertEquals($student->id, $loggedActivity->data['student_id']);
    }

    public function test_an_admin_teacher_is_unauthorized_to_delete_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.students.destroy', $student));

        // Assert that the response has a 404 “Not Found” status code.
        $response->assertNotFound();

        // Assert that the student was not soft-deleted.
        $this->assertNotSoftDeleted('students', ['id' => $student->id]);

        // Assert that no activity was logged.
        $this->assertDatabaseEmpty('activities');
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_delete_a_student_in_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.students.destroy', $student));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that the student was not soft-deleted.
        $this->assertNotSoftDeleted('students', ['id' => $student->id]);

        // Assert that no activity was logged.
        $this->assertDatabaseEmpty('activities');
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_delete_a_student_in_another_school()
    {
        $school1 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.students.destroy', $student));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that the student was not soft-deleted.
        $this->assertNotSoftDeleted('students', ['id' => $student->id]);

        // Assert that no activity was logged.
        $this->assertDatabaseEmpty('activities');
    }
}
