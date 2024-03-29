<?php

namespace Tests\Unit\Services;

use App\Models\Users\Student;
use App\Models\Users\StudentSetting;
use App\Models\Users\User;
use App\Services\StudentService;
use Tests\TestCase;

class StudentServiceTest extends TestCase
{
    protected StudentService $studentService;

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

        $this->assertCount(20, $result);
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

        $result = $this->studentService->find($student->id, ['with_classrooms' => true]);

        $this->assertTrue($result->relationLoaded('classroomGroups'));
        $this->assertCount(2, $result->classroomGroups);
    }

    /**
     * @see StudentService::create()
     */
    public function test_it_creates_a_student(): void
    {
        $school = $this->fakeSchool();

        $options = [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'username' => fake()->userName,
            'email' => fake()->safeEmail,
            'password' => fake()->password,
            'school_id' => $school->id,
            'settings' => [
                'expired_tasks_excluded' => fake()->boolean,
                'confetti_enabled' => fake()->boolean,
            ],
        ];

        $userCount = User::count();
        $studentCount = Student::count();
        $studentSettingsCount = StudentSetting::count();

        $result = $this->studentService->create($options);

        // Assert that it returns the created student.
        $this->assertInstanceOf(Student::class, $result);
        $this->assertStudentAttributes($options, $result);

        // Assert that the student was created correctly in the database.
        $this->assertDatabaseCount('students', $studentCount + 1);
        $student = Student::find($result->id);
        $this->assertStudentAttributes($options, $student);
    }

    /**
     * @see StudentService::update()
     */
    public function test_it_updates_a_student(): void
    {
        $school = $this->fakeTraditionalSchool();
        $student = $this->fakeStudent($school);

        $options = [
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'email' => fake()->safeEmail,
            'username' => fake()->userName,
            'password' => fake()->password,
        ];

        $result = $this->studentService->update($student, $options);

        // Assert that it returns an updated student.
        $this->assertEquals($options['first_name'], $result->first_name);
        $this->assertEquals($options['last_name'], $result->last_name);
        $this->assertEquals($options['username'], $result->username);
        $this->assertEquals($options['email'], $result->email);
        $this->assertObjectNotHasProperty('password', $result);

        // Assert that the student was updated in the database.
        $student->refresh();
        $this->assertEquals($options['first_name'], $student->first_name);
        $this->assertEquals($options['last_name'], $student->last_name);
        $this->assertEquals($options['username'], $student->username);
        $this->assertEquals($options['email'], $student->email);
    }

    /**
     * @see StudentService::delete()
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

        $this->studentService->delete($student);

        // Assert that the student was softly deleted.
        $this->assertSoftDeleted('students', ['id' => $student->id]);

        // Assert that the student was removed from the classroom groups.
        $this->assertDatabaseMissing('classroom_group_student', ['student_id' => $student->id]);
    }

    /**
     * @see StudentService::addToClassroomGroups()
     */
    public function test_it_adds_the_student_into_classroom_groups()
    {
        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeTeacher($school);
        $student = $this->fakeStudent($school);

        $classroom1 = $this->fakeClassroom($teacher);
        $customClassroomGroup1 = $this->fakeCustomClassroomGroup($classroom1);
        $classroom2 = $this->fakeClassroom($teacher);
        $customClassroomGroup2 = $this->fakeCustomClassroomGroup($classroom2);

        $options = [
            'expired_tasks_excluded' => fake()->boolean,
            'detaching' => false,
        ];

        // Assign the student into $customClassroomGroup1 and $customClassroomGroup2.
        $this->studentService->addToClassroomGroups($student,
            [
                $customClassroomGroup1->id,
                $customClassroomGroup2->id,
            ],
            $options);

        // Assert that the student was assigned into $customClassroomGroup1 and $customClassroomGroup2.
        $this->assertDatabaseCount('classroom_group_student', 2);
        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $customClassroomGroup1->id,
        ]);
        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $customClassroomGroup2->id,
        ]);

        // Assign the student into the default classroom group of $classroom1 and $customClassroomGroup2.
        $this->studentService->addToClassroomGroups($student,
            [
                $classroom1->defaultClassroomGroup->id,
                $customClassroomGroup2->id
            ],
            $options);

        // Assert that the student was assigned into the default classroom group of $classroom1 without duplication.
        $this->assertDatabaseCount('classroom_group_student', 3);
        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $classroom1->defaultClassroomGroup->id,
        ]);
        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $customClassroomGroup1->id,
        ]);
        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $customClassroomGroup2->id,
        ]);
    }

    /**
     * @see StudentService::addToClassroomGroups()
     */
    public function test_it_adds_the_student_into_classroom_groups_with_detaching_by_default()
    {
        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeTeacher($school);
        $student = $this->fakeStudent($school);

        $classroom1 = $this->fakeClassroom($teacher);
        $customClassroomGroup1 = $this->fakeCustomClassroomGroup($classroom1);
        $classroom2 = $this->fakeClassroom($teacher);
        $customClassroomGroup2 = $this->fakeCustomClassroomGroup($classroom2);

        $options = [
            'expired_tasks_excluded' => fake()->boolean,
        ];

        // Assign the student into $customClassroomGroup1 and $customClassroomGroup2.
        $this->studentService->addToClassroomGroups($student,
            [
                $customClassroomGroup1->id,
                $customClassroomGroup2->id,
            ],
            $options);

        // Assert that the student was assigned into $customClassroomGroup1 and $customClassroomGroup2.
        $this->assertDatabaseCount('classroom_group_student', 2);
        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $customClassroomGroup1->id,
        ]);
        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $customClassroomGroup2->id,
        ]);

        // Assign the student into the default classroom group of $classroom1 and $customClassroomGroup2.
        $this->studentService->addToClassroomGroups($student,
            [
                $classroom1->defaultClassroomGroup->id,
                $customClassroomGroup2->id
            ],
            $options);

        // Assert that the student was assigned with detaching $customClassroomGroup1.
        $this->assertDatabaseCount('classroom_group_student', 2);
        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $classroom1->defaultClassroomGroup->id,
        ]);
        $this->assertDatabaseMissing('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $customClassroomGroup1->id,
        ]);
        $this->assertDatabaseHas('classroom_group_student', [
            'student_id' => $student->id,
            'classroom_group_id' => $customClassroomGroup2->id,
        ]);
    }
}
