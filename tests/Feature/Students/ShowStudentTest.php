<?php

namespace Feature\Students;

use App\Http\Controllers\Api\V1\StudentController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see StudentController::show()
 */
class ShowStudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_unauthenticated_get_the_detail_of_a_student(): void
    {
        $student = $this->fakeStudent();

        $response = $this->getJson(route('api.v1.students.show', $student));

        // Assert that the request is unauthorized.
        $response->assertUnauthorized();
    }

    public function test_an_admin_teacher_can_get_the_detail_of_a_student_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.students.show', $student));

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonFragment(['success' => true]);
    }

    public function test_an_admin_teacher_is_unauthorized_to_get_the_detail_of_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.students.show', $student));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_a_non_admin_teacher_can_get_the_detail_of_a_student_who_is_in_the_classroom_owned_by_him(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);
        $this->attachStudentsToClassroomGroup($classroom->defaultClassroomGroup, [$student->id]);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.students.show', $student));

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonFragment(['success' => true]);
    }

    public function test_a_non_admin_teacher_can_get_the_detail_of_a_student_who_is_in_the_classroom_where_the_teacher_is_a_secondary_teacher()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $classroom = $this->fakeClassroom($adminTeacher);
        $this->attachSecondaryTeachersToClassroom($classroom, [$nonAdminTeacher->id]);
        $this->attachStudentsToClassroomGroup($classroom->defaultClassroomGroup, [$student->id]);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.students.show', $student));

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonFragment(['success' => true]);
    }

    public function test_a_non_admin_teacher_cannot_get_the_detail_of_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.students.show', $student));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_a_non_admin_teacher_cannot_get_the_detail_of_a_student_who_is_not_managed_by_him(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.students.show', $student));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_it_returns_expected_attributes_of_the_student()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.students.show', $student));

        // Assert that the request contains the expected attributes.
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'username',
                    'email',
                    'first_name',
                    'last_name',
                    'school_id',
                    'classroom_groups',
                    'classrooms',
                    'pass_grade',
                    'login_count',
                    'last_login_at',
                ],
            ])
            ->assertJsonFragment([
                'id' => $student->id,
                'username' => $student->username,
                'email' => $student->email,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'school_id' => $student->school_id,
            ]);
    }
}
