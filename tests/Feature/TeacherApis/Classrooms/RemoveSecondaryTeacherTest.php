<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use App\Http\Controllers\Api\Teachers\V1\ClassroomSecondaryTeacherController;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see ClassroomSecondaryTeacherController::destroy()
 */
class RemoveSecondaryTeacherTest extends TestCase
{
    use RefreshDatabase;

    protected string $seeder = MarketSeeder::class;

    public function test_admin_teacher_can_remove_secondary_teachers_from_classrooms_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);
        $this->attachSecondaryTeachers($classroom, [$teacher->id]);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.secondary-teachers.destroy', [
            'classroom' => $classroom->id,
            'teacher' => $teacher->id,
        ]));

        // Assert that the request is successful.
        $response->assertNoContent();
    }

    public function test_admin_teacher_cannot_remove_secondary_teachers_from_classrooms_in_other_schools(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $teacher1 = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $teacher2 = $this->fakeNonAdminTeacher($school2);
        $classroom = $this->fakeClassroom($teacher2);
        $this->attachSecondaryTeachers($classroom, [$teacher2->id]);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.secondary-teachers.destroy', [
            'classroom' => $classroom->id,
            'teacher' => $teacher1->id,
        ]));

        // Assert that the response has a 404 “Not Found” status code.
        $response->assertNotFound();
    }

    public function test_admin_teacher_cannot_remove_secondary_teachers_in_other_schools(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $teacher1 = $this->fakeNonAdminTeacher($school1);
        $classroom = $this->fakeClassroom($teacher1);

        $school2 = $this->fakeTraditionalSchool();
        $teacher2 = $this->fakeNonAdminTeacher($school2);

        $this->attachSecondaryTeachers($classroom, [$teacher2->id]);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.secondary-teachers.destroy', [
            'classroom' => $classroom->id,
            'teacher' => $teacher2->id,
        ]));

        // Assert that the response has a 404 “Not Found” status code.
        $response->assertNotFound();
    }

    public function test_admin_teacher_cannot_remove_a_teacher_from_a_classroom_if_the_teacher_is_not_the_secondary_teacher_of_the_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.secondary-teachers.destroy', [
            'classroom' => $classroom->id,
            'teacher' => $teacher->id,
        ]));

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }

    public function test_non_admin_teacher_are_unauthorized_to_remove_secondary_teachers_from_classrooms(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);
        $this->attachSecondaryTeachers($classroom, [$teacher->id]);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.secondary-teachers.destroy', [
            'classroom' => $classroom->id,
            'teacher' => $teacher->id,
        ]));

        // Assert that the response has a 403 status code.
        $response->assertForbidden();
    }
}
