<?php

namespace Tests\Feature\Classrooms;

use Tests\TestCase;

class IndexClassroomStudentTest extends TestCase
{
    public function test_a_guest_is_unauthenticated_to_get_the_list_of_students_in_a_classroom(): void
    {
        $this->assertGuest();

        $classroom = $this->fakeClassroom($this->fakeTeacher());

        $response = $this->getJson(
            route('api.v1.classrooms.students.index', [
                'classroom' => $classroom->id,
            ])
        );

        // Assert that the request is unauthorized.
        $response->assertUnauthorized();
    }

    public function test_an_admin_teacher_can_get_the_list_of_students_in_a_classroom_from_the_same_school()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(
            route('api.v1.classrooms.students.index', [
                'classroom' => $classroom->id,
            ])
        );

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonSuccess();
    }

    public function test_an_admin_teacher_is_unauthorized_to_get_the_list_of_students_in_a_classroom_from_another_school()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher1 = $this->fakeAdminTeacher($school1);
        $adminTeacher2 = $this->fakeAdminTeacher($school2);

        $classroom = $this->fakeClassroom($adminTeacher2);

        $this->actingAsTeacher($adminTeacher1);

        $response = $this->getJson(
            route('api.v1.classrooms.students.index', [
                'classroom' => $classroom->id,
            ])
        );

        // Assert that the request is forbidden.
        $response->assertForbidden();
    }

    public function test_a_non_admin_teacher_can_get_the_list_of_students_in_a_classroom_that_is_managed_by_them()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        // Create a classroom of which the non-admin teacher is the secondary teacher.
        $classroom1 = $this->fakeClassroom($adminTeacher);
        $this->attachSecondaryTeachersToClassroom($classroom1, [$nonAdminTeacher->id]);

        // Create a classroom of which the non-admin teacher is the owner.
        $classroom2 = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(
            route('api.v1.classrooms.students.index', [
                'classroom' => $classroom1->id,
            ])
        );

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonSuccess();

        $response = $this->getJson(
            route('api.v1.classrooms.students.index', [
                'classroom' => $classroom2->id,
            ])
        );

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonSuccess();
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_get_the_list_of_students_in_a_classroom_that_is_not_managed_by_them()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher1 = $this->fakeAdminTeacher($school1);
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school1);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school2);

        // Create a classroom of which the admin teacher is the owner.
        $classroom1 = $this->fakeClassroom($adminTeacher1);

        // Create a classroom for $school2.
        $classroom2 = $this->fakeClassroom($nonAdminTeacher2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->getJson(
            route('api.v1.classrooms.students.index', [
                'classroom' => $classroom1->id,
            ])
        );

        // Assert that the request is forbidden.
        $response->assertForbidden();

        $response = $this->getJson(
            route('api.v1.classrooms.students.index', [
                'classroom' => $classroom2->id,
            ])
        );

        // Assert that the request is forbidden.
        $response->assertForbidden();
    }

    public function test_it_responses_with_expected_attributes()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $students = $this->fakeStudent($school, 5);

        // Add the students to the classroom.
        $this->attachStudentsToClassroomGroup($classroom->defaultClassroomGroup, $students->pluck('id')->toArray());

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(
            route('api.v1.classrooms.students.index', [
                'classroom' => $classroom->id,
            ])
        );

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonSuccess();

        // Assert that the response data has the expected structure.
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'username',
                    'first_name',
                    'last_name',
                    'email',
                    'classroom_groups' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                    'login_count',
                    'last_login_at',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    public function test_the_admin_teacher_gets_the_correct_list_of_students_in_the_classroom()
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher1 = $this->fakeAdminTeacher($school1);
        $classroom1 = $this->fakeClassroom($adminTeacher1);
        $students1 = $this->fakeStudent($school1, 5);
        $this->attachStudentsToClassroomGroup($classroom1->defaultClassroomGroup, $students1->pluck('id')->toArray());

        $school2 = $this->fakeTraditionalSchool();
        $adminTeacher2 = $this->fakeAdminTeacher($school2);
        $classroom2 = $this->fakeClassroom($adminTeacher2);
        $students2 = $this->fakeStudent($school2, 5);
        $this->attachStudentsToClassroomGroup($classroom2->defaultClassroomGroup, $students2->pluck('id')->toArray());

        $this->actingAsTeacher($adminTeacher1);

        $response = $this->getJson(
            route('api.v1.classrooms.students.index', [
                'classroom' => $classroom1->id,
            ])
        );

        $response->assertOk()
            ->assertJsonSuccess();

        // Assert that the response is correct.
        $response->assertJsonCount(5, 'data');

        foreach ($students1 as $student) {
            $response->assertJsonFragment(['id' => $student->id]);
        }

        foreach ($students2 as $student) {
            $response->assertJsonMissing(['id' => $student->id]);
        }
    }

    public function test_the_non_admin_teacher_gets_the_correct_list_of_students_in_the_classroom()
    {
        $school1 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);
        $classroom1 = $this->fakeClassroom($nonAdminTeacher);
        $students1 = $this->fakeStudent($school1, 5);
        $this->attachStudentsToClassroomGroup($classroom1->defaultClassroomGroup, $students1->pluck('id')->toArray());

        $school2 = $this->fakeTraditionalSchool();
        $adminTeacher2 = $this->fakeAdminTeacher($school2);
        $classroom2 = $this->fakeClassroom($adminTeacher2);
        $students2 = $this->fakeStudent($school2, 5);
        $this->attachStudentsToClassroomGroup($classroom2->defaultClassroomGroup, $students2->pluck('id')->toArray());

        $classroom3 = $this->fakeClassroom($nonAdminTeacher);
        $students3 = $this->fakeStudent($school1, 5);
        $this->attachStudentsToClassroomGroup($classroom3->defaultClassroomGroup, $students3->pluck('id')->toArray());

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(
            route('api.v1.classrooms.students.index', [
                'classroom' => $classroom1->id,
            ])
        );

        $response->assertOk()
            ->assertJsonSuccess();

        // Assert that the response is correct.
        $response->assertJsonCount(5, 'data');

        foreach ($students1 as $student) {
            $response->assertJsonFragment(['id' => $student->id]);
        }

        foreach ($students2 as $student) {
            $response->assertJsonMissing(['id' => $student->id]);
        }

        foreach ($students3 as $student) {
            $response->assertJsonMissing(['id' => $student->id]);
        }
    }
}
