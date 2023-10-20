<?php

namespace Feature\Students;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexStudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_get_the_list_of_students(): void
    {
        $this->fakeStudent(null, 5);

        $this->assertGuest();

        $response = $this->getJson(route('api.v1.students.index'));

        // Assert that the request is unauthorized.
        $response->assertUnauthorized();
    }

    public function test_an_admin_teacher_can_get_the_list_of_all_students_who_are_in_the_same_school_as_the_teacher(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $students1 = $this->fakeStudent($school1, 5);

        $school2 = $this->fakeTraditionalSchool();
        $students2 = $this->fakeStudent($school2, 5);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.students.index', [
            'all' => true,
        ]));

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        // Assert that the response contains the correct number of students.
        $response->assertJsonCount($students1->count(), 'data');

        // Assert all students in school 2 are not included.
        foreach ($students2 as $student) {
            $response->assertJsonMissing([
                'id' => $student->id,
            ]);
        }

        // Assert all students in school 1 are included.
        foreach ($students1 as $student) {
            $response->assertJsonFragment([
                'id' => $student->id,
            ]);
        }
    }

    public function test_an_admin_teacher_can_filter_the_list_of_students_who_are_in_the_classrooms_of_which_he_is_the_owner_or_the_secondary_teacher()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        // Add students to a classroom that is not related to the admin teacher.
        $classroom1 = $this->fakeClassroom($nonAdminTeacher);
        $students1 = $this->fakeStudent($school, 5);
        $this->attachStudentsToClassroomGroup($classroom1->defaultClassroomGroup, $students1->pluck('id')->toArray());

        // Add students to a classroom that is owned by the admin teacher.
        $classroom2 = $this->fakeClassroom($adminTeacher);
        $students2 = $this->fakeStudent($school, 5);
        $this->attachStudentsToClassroomGroup($classroom2->defaultClassroomGroup, $students2->pluck('id')->toArray());

        // Add students to a classroom fo which the admin teacher is a secondary teacher.
        $classroom3 = $this->fakeClassroom($nonAdminTeacher);
        $this->attachSecondaryTeachersToClassroom($classroom3, [$adminTeacher->id]);
        $students3 = $this->fakeStudent($school, 5);
        $this->attachStudentsToClassroomGroup($classroom3->defaultClassroomGroup, $students3->pluck('id')->toArray());

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.students.index', [
            'all' => false,
        ]));

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        // Assert that the response contains the correct number of students.
        $response->assertJsonCount($students2->count() + $students3->count(), 'data');

        // Assert all students in classroom 1 are not included.
        foreach ($students1 as $student) {
            $response->assertJsonMissing([
                'id' => $student->id,
            ]);
        }

        // Assert all students in classroom 2 are included.
        foreach ($students2 as $student) {
            $response->assertJsonFragment([
                'id' => $student->id,
            ]);
        }

        // Assert all students in classroom 3 are included.
        foreach ($students3 as $student) {
            $response->assertJsonFragment([
                'id' => $student->id,
            ]);
        }
    }

    public function test_an_admin_teacher_can_fuzzy_search_students(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $student1 = $this->fakeStudent($school, 1, [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'john.doe',
        ]);

        $student2 = $this->fakeStudent($school, 1, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'jane.doe',
        ]);

        $student3 = $this->fakeStudent($school, 1, [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'username' => 'john.smith',
        ]);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.students.index', [
            'all' => true,
            'search_key' => 'john',
        ]));

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        // Assert that the response contains the correct number of students.
        $response->assertJsonCount(2, 'data');

        // Assert that $student1 is not included.
        $response->assertJsonFragment([
            'id' => $student1->id,
        ]);

        // Assert that $student2 is not included.
        $response->assertJsonMissing([
            'id' => $student2->id,
        ]);

        // Assert that $student3 is included.
        $response->assertJsonFragment([
            'id' => $student3->id,
        ]);
    }

    public function test_a_non_admin_teacher_can_only_get_the_list_of_students_who_are_in_the_classrooms_of_which_he_is_the_owner_or_the_secondary_teacher(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);

        // Add students to a classroom that is not related to the non-admin teacher.
        $classroom1 = $this->fakeClassroom($adminTeacher);
        $students1 = $this->fakeStudent($school1, 5);
        $this->attachStudentsToClassroomGroup($classroom1->defaultClassroomGroup, $students1->pluck('id')->toArray());

        // Add students to a classroom that is owned by the non-admin teacher.
        $classroom2 = $this->fakeClassroom($nonAdminTeacher);
        $students2 = $this->fakeStudent($school1, 5);
        $this->attachStudentsToClassroomGroup($classroom2->defaultClassroomGroup, $students2->pluck('id')->toArray());

        // Add students to a classroom fo which the non-admin teacher is a secondary teacher.
        $classroom3 = $this->fakeClassroom($adminTeacher);
        $this->attachSecondaryTeachersToClassroom($classroom3, [$nonAdminTeacher->id]);
        $students3 = $this->fakeStudent($school1, 5);
        $this->attachStudentsToClassroomGroup($classroom3->defaultClassroomGroup, $students3->pluck('id')->toArray());

        // Add students to a classroom in another school.
        $school2 = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school2);
        $classroom4 = $this->fakeClassroom($teacher);
        $students4 = $this->fakeStudent($school2, 5);
        $this->attachStudentsToClassroomGroup($classroom4->defaultClassroomGroup, $students4->pluck('id')->toArray());

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.students.index', [
            'all' => true,
        ]));

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        // Assert that the response contains the correct number of students.
        $response->assertJsonCount($students2->count() + $students3->count(), 'data');

        // Assert that all students in classroom 1 are not included.
        foreach ($students1 as $student) {
            $response->assertJsonMissing([
                'id' => $student->id,
            ]);
        }

        // Assert that all students in classroom 2 are included.
        foreach ($students2 as $student) {
            $response->assertJsonFragment([
                'id' => $student->id,
            ]);
        }

        // Assert that all students in classroom 3 are included.
        foreach ($students3 as $student) {
            $response->assertJsonFragment([
                'id' => $student->id,
            ]);
        }

        // Assert that all students in classroom 4 are not included.
        foreach ($students4 as $student) {
            $response->assertJsonMissing([
                'id' => $student->id,
            ]);
        }
    }

    public function test_a_non_admin_teacher_can_fuzzy_search_students(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $student1 = $this->fakeStudent($school, 1, [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'john.doe',
        ]);

        $student2 = $this->fakeStudent($school, 1, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'username' => 'jane.doe',
        ]);

        $student3 = $this->fakeStudent($school, 1, [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'username' => 'john.smith',
        ]);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->attachStudentsToClassroomGroup($classroom->defaultClassroomGroup, [$student1->id, $student2->id]);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.students.index', [
            'search_key' => 'john',
        ]));

        // Assert that the request is successful.
        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        // Assert that the response contains the correct number of students.
        $response->assertJsonCount(1, 'data');

        // Assert that $student1 is not included.
        $response->assertJsonFragment([
            'id' => $student1->id,
        ]);

        // Assert that $student2 is not included.
        $response->assertJsonMissing([
            'id' => $student2->id,
        ]);

        // Assert that $student3 is not included.
        $response->assertJsonMissing([
            'id' => $student3->id,
        ]);
    }

    public function test_it_returns_expected_details_of_students()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->fakeStudent($school, 5);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.students.index'));

        // Assert the response has the expected attributes of each student.
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'school_id',
                    'username',
                    'first_name',
                    'last_name',
                ],
            ]
        ]);

        // Assert the response does not contain the password of each student.
        $response->assertJsonMissingPath('data.*.password');
    }

    public function test_it_returns_login_statistics_if_explicitly_requested()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->fakeStudent($school, 5);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.students.index', [
            'with_activities' => true,
        ]));

        // Assert the response has the expected attributes of each student.
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'login_count',
                    'last_login_at',
                ],
            ]
        ]);
    }

}
