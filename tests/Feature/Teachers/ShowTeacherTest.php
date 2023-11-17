<?php

namespace Tests\Feature\Teachers;

use App\Http\Controllers\Api\V1\TeacherController;
use Tests\TestCase;

/**
 * Test teacher showing endpoint for teachers.
 *
 * @see /routes/api/api-teachers.php
 * @see TeacherController::show()
 */
class ShowTeacherTest extends TestCase
{
    public function test_a_guest_cannot_get_the_details_of_a_teacher(): void
    {
        $teacher = $this->fakeTeacher();

        $this->assertGuest();

        $response = $this->getJson(route('api.v1.teachers.show', $teacher->id));

        // Assert that the request is unauthorized.
        $response->assertUnauthorized();
    }

    public function test_an_admin_teacher_can_get_the_details_of_a_teacher_in_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.teachers.show', $teacher->id));

        // Assert that the request is successful.
        $response->assertOk()->assertJsonSuccessful();

        // Assert that the teacher profile is correct.
        $response->assertJsonFragment(['id' => $teacher->id]);
    }

    public function test_a_non_admin_teacher_are_unauthorised_to_get_the_details_of_another_teacher_in_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $teacher1 = $this->fakeNonAdminTeacher($school);
        $teacher2 = $this->fakeTeacher($school);

        $this->actingAsTeacher($teacher1);

        $response = $this->getJson(route('api.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_an_admin_teacher_is_unauthorized_to_get_the_details_of_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $teacher1 = $this->fakeAdminTeacher($school1);
        $teacher2 = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($teacher1);

        $response = $this->getJson(route('api.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_a_non_admin_teacher_is_unauthorised_to_view_the_details_of_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $teacher1 = $this->fakeNonAdminTeacher($school1);
        $teacher2 = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($teacher1);

        $response = $this->getJson(route('api.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_an_admin_teacher_cannot_view_the_details_of_a_soft_deleted_teacher_in_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school, 1, ['deleted_at' => now()]);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.teachers.show', $teacher->id));

        // Assert that the soft deleted teacher is not found.
        $response->assertNotFound();
    }

    public function test_an_admin_teacher_can_view_limited_attributes_of_the_teacher_by_default(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.teachers.show', $teacher->id));

        // Assert the response has the expected attributes of the teacher.
        $response->assertJsonStructure([
            'data' => [
                'id',
                'school_id',
                'username',
                'first_name',
                'last_name',
                'email',
                'title',
                'position',
                'is_admin',
            ],
        ]);

        // Assert the response does not contain the relationships of the teacher.
        $response->assertJsonMissing([
            'data' => [
                'school',
                'owned_classrooms',
                'secondary_classrooms',
            ],
        ]);
    }

    public function test_an_admin_teacher_can_view_the_school_of_the_teacher_if_explicitly_requested(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.teachers.show', [
            'teacher' => $teacher->id,
            'with_school' => true,
        ]));

        // Assert the response has the expected attributes of the teacher.
        $response->assertJsonStructure([
            'data' => [
                'school',
            ],
        ]);
    }

    public function test_an_admin_teacher_can_view_the_classrooms_of_the_teacher_if_explicitly_requested(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.teachers.show', [
            'teacher' => $teacher->id,
            'with_classrooms' => true,
        ]));

        // Assert the response has the expected attributes of the teacher.
        $response->assertJsonStructure([
            'data' => [
                'owned_classrooms',
                'secondary_classrooms',
            ],
        ]);
    }
}
