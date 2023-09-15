<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use App\Http\Controllers\Api\Teachers\V1\ClassroomGroupController;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see ClassroomGroupController::destroy()
 */
class DeleteClassroomGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_teachers_can_delete_groups_from_classrooms_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $customGroup = $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.groups.destroy', [$classroom->id, $customGroup->id]));

        // Assert that the response has 204 status code and no content.
        $response->assertNoContent();
    }

    public function test_admin_teachers_are_unauthorized_to_delete_groups_from_classrooms_in_the_same_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher1 = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $adminTeacher2 = $this->fakeAdminTeacher($school2);
        $classroom = $this->fakeClassroom($adminTeacher2);
        $customGroup = $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($adminTeacher1);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.groups.destroy', [$classroom->id, $customGroup->id]));

        // Assert that the response has 403 status code.
        $response->assertForbidden();
    }

    public function test_non_admin_teachers_can_delete_groups_from_classrooms_that_they_own(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $customGroup = $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.groups.destroy', [$classroom->id, $customGroup->id]));

        // Assert that the response has 204 status code and no content.
        $response->assertNoContent();
    }

    public function test_non_admin_teachers_are_unauthorized_to_delete_groups_from_classrooms_that_they_do_not_own(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);

        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher2);
        $customGroup = $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.groups.destroy', [$classroom->id, $customGroup->id]));

        // Assert that the response has 403 status code.
        $response->assertForbidden();
    }

    public function test_teachers_cannot_delete_the_default_group_without_deleting_the_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.groups.destroy', [$classroom->id, $classroom->defaultClassroomGroup->id]));

        // Assert that the response has 403 status code.
        $response->assertForbidden();
    }

    public function test_teachers_cannot_delete_the_group_that_does_not_belong_to_the_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($nonAdminTeacher);
        $classroom2 = $this->fakeClassroom($nonAdminTeacher);

        $customGroup = $this->fakeCustomClassroomGroup($classroom2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.groups.destroy', [$classroom1->id, $customGroup->id]));

        // Assert that the response has 404 "Not Found" status code.
        $response->assertNotFound();
    }
}
