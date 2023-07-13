<?php

namespace Tests\Unit\Services;

use App\Models\Users\Student;
use App\Services\StudentService;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StudentService $studentService;

    /**
     * Run MarketSeeder before each test.
     *
     * @var string
     */
    protected string $seeder = MarketSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentService = new StudentService();
    }

    /**
     * @see StudentService::search()
     */
    public function test_it_searches_students_by_school_id(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $students1 = $this->fakeStudent($school1, 3);

        $school2 = $this->fakeTraditionalSchool();
        $students2 = $this->fakeStudent($school2, 3);

        $options = [
            'school_id' => $school1->id,
        ];

        $result = $this->studentService->search($options);

        // Assert that the result contains only students from school1.
        $this->assertCount($students1->count(), $result);

        foreach ($students1 as $student) {
            $this->assertContains($student->id, $result->pluck('id'));
        }

        foreach ($students2 as $student) {
            $this->assertNotContains($student->id, $result->pluck('id'));
        }
    }

    /**
     * @see StudentService::search()
     */
    public function test_it_searches_students_by_classroom_ids(): void
    {
        $school = $this->fakeTraditionalSchool();

        $teacher1 = $this->fakeAdminTeacher($school);
        $teacher2 = $this->fakeAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($teacher1);
        $students1 = $this->fakeStudent($school, 3);
        $this->attachStudentsToClassroomGroup($classroom1->defaultClassroomGroup, $students1->pluck('id')->toArray());

        $students2 = $this->fakeStudent($school, 3);
        $classroom2 = $this->fakeClassroom($teacher1);
        $this->attachStudentsToClassroomGroup($classroom2->defaultClassroomGroup, $students2->pluck('id')->toArray());

        $students3 = $this->fakeStudent($school, 3);
        $classroom3 = $this->fakeClassroom($teacher2);
        $this->attachStudentsToClassroomGroup($classroom3->defaultClassroomGroup, $students3->pluck('id')->toArray());

        $options = [
            'school_id' => $school->id,
            'classroom_ids' => [$classroom1->id, $classroom2->id],
        ];

        $result = $this->studentService->search($options);

        // Assert that the result contains only students from classroom1 and classroom2.
        $this->assertCount($students1->count() + $students2->count(), $result);

        foreach ($students1 as $student) {
            $this->assertContains($student->id, $result->pluck('id'));
        }

        foreach ($students2 as $student) {
            $this->assertContains($student->id, $result->pluck('id'));
        }

        foreach ($students3 as $student) {
            $this->assertNotContains($student->id, $result->pluck('id'));
        }
    }

    /**
     * @see StudentService::search()
     */
    public function test_it_searches_students_by_search_key(): void
    {
        $school = $this->fakeTraditionalSchool();

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

        $options = [
            'school_id' => $school->id,
            'key' => 'John',
        ];

        $result = $this->studentService->search($options);

        $this->assertCount(2, $result);
        $this->assertContains($student1->id, $result->pluck('id'));
        $this->assertNotContains($student2->id, $result->pluck('id'));
        $this->assertContains($student3->id, $result->pluck('id'));
    }

    /**
     * @see StudentService::search()
     */
    public function test_it_paginates_search_result_by_default()
    {
        $school = $this->fakeTraditionalSchool();

        $this->fakeStudent($school, 30);

        $options = [
            'school_id' => $school->id,
        ];

        $result = $this->studentService->search($options);

        $this->assertCount(15, $result);
        $this->assertEquals(1, $result->currentPage());
    }

    /**
     * @see StudentService::search()
     */
    public function test_it_can_return_search_result_without_pagination()
    {
        $school = $this->fakeTraditionalSchool();

        $students = $this->fakeStudent($school, 30);

        $options = [
            'school_id' => $school->id,
            'pagination' => false,
        ];

        $result = $this->studentService->search($options);

        $this->assertCount($students->count(), $result);
    }

    /**
     * @see StudentService::find()
     */
    public function test_it_finds_student_by_id(): void
    {
        $school = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school);

        $result = $this->studentService->find($student->id);

        $this->assertEquals($student->id, $result->id);
    }

    /**
     * @see StudentService::find()
     */
    public function test_it_finds_student_with_classroom_groups(): void
    {
        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeAdminTeacher($school);

        $student = $this->fakeStudent($school);

        $classroom1 = $this->fakeClassroom($teacher);
        $this->attachStudentsToClassroomGroup($classroom1->defaultClassroomGroup, [$student->id]);

        $classroom2 = $this->fakeClassroom($teacher);
        $this->attachStudentsToClassroomGroup($classroom2->defaultClassroomGroup, [$student->id]);

        $result = $this->studentService->find($student->id, ['with_classroom_groups' => true]);

        $this->assertNotNull($result->classroomGroups);
        $this->assertCount(2, $result->classroomGroups);
    }

    /**
     * @see StudentService::update()
     */
    public function test_it_updates_a_student()
    {
        $school = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school);

        $options = [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'username' => fake()->userName,
            'password' => fake()->password,
        ];

        $result = $this->studentService->update($student, $options);

        // Assert that it returns an updated student.
        $this->assertEquals($options['first_name'], $result->first_name);
        $this->assertEquals($options['last_name'], $result->last_name);
        $this->assertEquals($options['username'], $result->username);
        $this->assertObjectNotHasProperty('password', $result);

        // Assert that the student was updated in the database.
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => $options['first_name'],
            'last_name' => $options['last_name'],
            'username' => $options['username'],
        ]);

        // Assert that the student's password was updated in the database.
        $password = Student::find($student->id)->password;
        $this->assertTrue(Hash::check($options['password'], $password));
    }

    /**
     * @see StudentService::softDelete()
     */
    public function test_it_soft_deletes_a_student(): void
    {
        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($teacher);
        $classroom2 = $this->fakeClassroom($teacher);

        $student = $this->fakeStudent($school);

        $this->attachStudentsToClassroomGroup($classroom1->defaultClassroomGroup, [$student->id]);
        $this->attachStudentsToClassroomGroup($classroom2->defaultClassroomGroup, [$student->id]);

        $this->studentService->softDelete($student);

        // Assert that the student was softly deleted.
        $this->assertSoftDeleted('students', ['id' => $student->id]);

        // Assert that the student was removed from the classroom groups.
        $this->assertDatabaseMissing('classroom_group_student', ['student_id' => $student->id]);
    }
}
