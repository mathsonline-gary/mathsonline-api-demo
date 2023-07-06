<?php

namespace Tests\Feature\TeacherApis\Students;

use App\Http\Controllers\Api\Teachers\V1\StudentController;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see StudentController::show()
 */
class ShowStudentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run MarketSeeder before each test.
     *
     * @var string
     */
    protected string $seeder = MarketSeeder::class;

    public function test_an_admin_teacher_can_get_the_detail_of_a_student_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.teachers.v1.students.show', $student));

        // Assert that the request is successful.
        $response->assertOk();

        // Assert the response has the expected attributes of the student.
        $response->assertJsonStructure([
            'data' => [
                'id',
                'school_id',
                'username',
                'first_name',
                'last_name',
                'classroom_groups',
            ],
        ]);

        // Assert the response does not contain the password of the student.
        $response->assertJsonMissingPath('data.password');

        // Assert the response has the expected value of each attribute.
        $response->assertJson([
            'data' => [
                'id' => $student->id,
                'school_id' => $student->school_id,
                'username' => $student->username,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
            ],
        ]);
    }

    public function test_an_admin_teacher_cannot_get_the_detail_of_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.teachers.v1.students.show', $student));

        // Assert that the response has status code 404.
        $response->assertNotFound();
    }

    public function test_a_non_admin_teacher_can_get_the_detail_of_a_student_who_is_in_the_classroom_owned_by_him(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);
        $this->attachStudentsToClassroomGroup($classroom->defaultClassroomGroup, [$student->id]);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.students.show', $student));

        // Assert that the request is successful.
        $response->assertOk();

        // Assert the response has the expected attributes of the student.
        $response->assertJsonStructure([
            'data' => [
                'id',
                'school_id',
                'username',
                'first_name',
                'last_name',
                'school',
                'classroom_groups',
                'classrooms',
            ],
        ]);

        // Assert the response does not contain the password of the student.
        $response->assertJsonMissingPath('data.password');

        // Assert the response has the expected value of each attribute.
        $response->assertJson([
            'data' => [
                'id' => $student->id,
                'school_id' => $student->school_id,
                'username' => $student->username,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
            ],
        ]);
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

        $response = $this->getJson(route('api.teachers.v1.students.show', $student));

        // Assert that the request is successful.
        $response->assertOk();

        // Assert the response has the expected attributes of the student.
        $response->assertJsonStructure([
            'data' => [
                'id',
                'school_id',
                'username',
                'first_name',
                'last_name',
                'school',
                'classroom_groups',
                'classrooms',
            ],
        ]);

        // Assert the response does not contain the password of the student.
        $response->assertJsonMissingPath('data.password');

        // Assert the response has the expected value of each attribute.
        $response->assertJson([
            'data' => [
                'id' => $student->id,
                'school_id' => $student->school_id,
                'username' => $student->username,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
            ],
        ]);
    }

    public function test_a_non_admin_teacher_cannot_get_the_detail_of_a_student_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.students.show', $student));

        // Assert that the response has status code 404.
        $response->assertNotFound();
    }

    public function test_a_non_admin_teacher_cannot_get_the_detail_of_a_student_who_is_not_managed_by_him(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $student = $this->fakeStudent($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.students.show', $student));

        // Assert that the response has status code 404.
        $response->assertNotFound();
    }
}
