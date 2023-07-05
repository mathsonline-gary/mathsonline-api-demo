<?php

namespace Tests\Feature\TeacherApis\Students;

use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexStudentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run MarketSeeder before each test.
     *
     * @var string
     */
    protected string $seeder = MarketSeeder::class;

    public function test_an_admin_teacher_can_get_the_list_of_students_who_are_in_the_same_school_as_the_teacher(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $students1 = $this->fakeStudent($school1, 5);

        $school2 = $this->fakeTraditionalSchool();
        $students2 = $this->fakeStudent($school2, 5);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.teachers.v1.students.index'));

        // Assert that the request is successful.
        $response->assertOk();

        // Assert that the response contains the correct number of students.
        $response->assertJsonCount($students1->count(), 'data');

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

    public function test_an_admin_teacher_can_fuzzy_search_students(): void
    {

    }

    public function test_a_non_admin_teacher_can_get_the_list_of_students_who_are_in_the_classrooms_of_which_he_is_the_owner_or_the_secondary_teacher(): void
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

        $response = $this->getJson(route('api.teachers.v1.students.index'));

        // Assert that the request is successful.
        $response->assertOk();

        // Assert that the response contains the correct number of students.
        $response->assertJsonCount($students2->count() + $students3->count(), 'data');

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

    }
}
