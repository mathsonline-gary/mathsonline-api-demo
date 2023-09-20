<?php

namespace Tests\Feature\TeacherApis\Teachers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test teacher deletion endpoint for teachers.
 *
 * @see /routes/api/api-teachers.php
 * @see TeacherController::destroy()
 */
class DeleteTeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_teacher_can_delete_a_teacher_in_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $teacherAdmin = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        // Set the $teacher as the owner of $classroom1, and the secondary teacher of $classroom2.
        $classroom1 = $this->fakeClassroom($teacherAdmin);
        $classroom2 = $this->fakeClassroom($teacher);
        $this->attachSecondaryTeachersToClassroom($classroom1, [$teacher->id]);

        // Assert $teacher was set correctly
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id])
            ->assertDatabaseHas('classrooms', ['id' => $classroom2->id, 'owner_id' => $teacher->id])
            ->assertDatabaseHas('classroom_secondary_teacher', [
                'classroom_id' => $classroom1->id,
                'teacher_id' => $teacher->id,
            ]);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', $teacher));

        // Assert that the response returns no content
        $response->assertNoContent();

        // Assert that $teacher is soft-deleted
        $this->assertSoftDeleted('teachers', ['id' => $teacher->id]);

        // Assert that $teacher is removed as the owner of $classroom2, and as a secondary teacher of $classroom1.
        $this->assertDatabaseHas('classrooms', ['id' => $classroom2->id, 'owner_id' => null])
            ->assertDatabaseMissing('classroom_secondary_teacher', [
                'classroom_id' => $classroom1->id,
                'teacher_id' => $teacher->id,
            ]);
    }

    public function test_an_admin_teacher_is_unauthorised_to_delete_a_teacher_in_another_school()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $teacherAdmin = $this->fakeAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        // Assert that $teacher is in the database
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', $teacher));

        // Assert that the request is unauthorised
        $response->assertForbidden();

        // Assert that $teacher is not deleted
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
    }

    public function test_a_non_admin_teacher_is_unauthorised_to_delete_a_teacher_in_their_school()
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        // Assert that $teacher is in the database
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', $teacher));

        // Assert that the request is unauthorised
        $response->assertForbidden();

        // Assert that $teacher is not deleted
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
    }

    public function test_a_non_admin_teacher_is_unauthorised_to_delete_a_teacher_in_another_school()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        // Assert that $teacher is in the database
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', $teacher));

        // Assert that the request is unauthorised
        $response->assertForbidden();

        // Assert that $teacher is not deleted
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
    }
}
