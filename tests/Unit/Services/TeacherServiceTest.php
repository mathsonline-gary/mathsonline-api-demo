<?php

namespace Tests\Unit\Services;

use App\Models\Users\Teacher;
use App\Services\TeacherService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * @see TeacherService
 */
class TeacherServiceTest extends TestCase
{
    protected TeacherService $teacherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherService = new TeacherService();
    }

    /**
     * @see TeacherService::find()
     */
    public function test_it_finds_a_teacher(): void
    {
        $school = $this->fakeTraditionalSchool();

        $teacher = Teacher::factory()
            ->ofSchool($school)
            ->create();

        // Call the find method with options
        $foundTeacher = $this->teacherService->find($teacher->id, [
            'with_school' => true,
            'with_classrooms' => true,
        ]);

        // Assert that the teacher was found
        $this->assertInstanceOf(Teacher::class, $foundTeacher);

        // Assert that the found teacher's ID is correct
        $this->assertEquals($foundTeacher->id, $teacher->id);

        // Assert that the loaded relationships are correct
        $this->assertTrue($foundTeacher->relationLoaded('school'));
        $this->assertTrue($foundTeacher->relationLoaded('ownedClassrooms'));
        $this->assertTrue($foundTeacher->relationLoaded('secondaryClassrooms'));
    }

    /**
     * @see TeacherService::search()
     */
    public function test_it_searches_teachers_by_school_id()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $this->fakeAdminTeacher($school1, 5);
        $this->fakeAdminTeacher($school2, 5);

        $result = $this->teacherService->search([
            'school_id' => $school1->id,
        ]);

        // Assert that it returns a pagination by default.
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);

        // Assert that it returns the correct number of teachers.
        $this->assertCount(5, $result->items());

        // Assert that all teachers belong to school1.
        $this->assertTrue($result->every(function ($teacher) use ($school1) {
            return $teacher->school_id === $school1->id;
        }));

        // Assert that all teachers don't belong to school2.
        $this->assertFalse($result->contains(function ($teacher) use ($school2) {
            return $teacher->school_id === $school2->id;
        }));
    }

    /**
     * @see TeacherService::search()
     */
    public function test_it_fuzzy_searches_teachers()
    {
        $school = $this->fakeTraditionalSchool();

        $teacher1 = $this->fakeAdminTeacher($school, 1, [
            'username' => 'test',
            'email' => 'teacher1@test.com',
            'first_name' => 'Test',
            'last_name' => 'Test',
        ]);

        $teacher2 = $this->fakeAdminTeacher($school, 1, [
            'username' => 'john',
            'email' => 'teacher2@test.com',
            'first_name' => 'Test',
            'last_name' => 'Test',
        ]);

        $teacher3 = $this->fakeNonAdminTeacher($school, 1, [
            'first_name' => 'John',
            'username' => 'teacher3',
            'email' => 'teacher3@test.com',
            'last_name' => 'Test',
        ]);

        $teacher4 = $this->fakeNonAdminTeacher($school, 1, [
            'last_name' => 'John',
            'username' => 'teacher4',
            'email' => 'teacher4@test.com',
            'first_name' => 'Test',
        ]);

        $teacher5 = $this->fakeNonAdminTeacher($school, 1, [
            'email' => 'john@test.com',
            'username' => 'teacher5',
            'first_name' => 'Test',
            'last_name' => 'Test',
        ]);

        $result = $this->teacherService->search([
            'key' => 'joh',
        ]);

        // Assert that it returns a pagination by default.
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);

        // Assert that teachers in the result are correct.
        $this->assertFalse($result->contains($teacher1));
        $this->assertTrue($result->contains($teacher2));
        $this->assertTrue($result->contains($teacher3));
        $this->assertTrue($result->contains($teacher4));
        $this->assertTrue($result->contains($teacher5));
    }

    /**
     * @see TeacherService::search()
     */
    public function test_it_returns_search_result_without_pagination(): void
    {
        $school = $this->fakeTraditionalSchool();

        $this->fakeAdminTeacher($school, 10);

        $result = $this->teacherService->search([
            'pagination' => false,
        ]);

        // Assert that it returns the collection instead of pagination.
        $this->assertInstanceOf(Collection::class, $result);
    }

    /**
     * @see TeacherService::create()
     */
    public function test_it_creates_a_teacher(): void
    {
        $school = $this->fakeTraditionalSchool();

        $attributes = [
            'school_id' => $school->id,
            'username' => 'john_doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'title' => 'Mr',
            'position' => 'Maths Teacher',
            'is_admin' => false,
        ];

        $teacher = $this->teacherService->create($attributes);

        // Assert that the teacher was created correctly.
        $this->assertTeacherAttributes([
            ...$attributes,
            'deleted_at' => null,
        ], $teacher);
    }

    /**
     * @see TeacherService::update()
     */
    public function test_it_updates_a_teacher()
    {
        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeAdminTeacher($school);

        $attributes = [
            'username' => 'john_doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'title' => 'Mr',
            'position' => 'Maths Teacher',
            'is_admin' => false,
        ];

        $result = $this->teacherService->update($teacher, $attributes);

        // Assert that it returns the updated teacher.
        $expected = [
            ...$attributes,
            'id' => $teacher->id,
            'school_id' => $school->id,
            'deleted_at' => null,
        ];

        $this->assertInstanceOf(Teacher::class, $result);
        $this->assertTeacherAttributes($expected, $result);

        // Assert that the teacher was updated correctly.
        $teacher->refresh();
        $this->assertTeacherAttributes($expected, $teacher);
    }

    /**
     * @see TeacherService::delete()
     */
    public function test_it_deletes_a_teacher()
    {
        $school = $this->fakeTraditionalSchool();

        $teacherAdmin = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeAdminTeacher($school);

        // Set the $teacher as the owner of $classroom1, and the secondary teacher of $classroom2.
        $classroom1 = $this->fakeClassroom($teacherAdmin);
        $classroom2 = $this->fakeClassroom($teacher);
        $this->attachSecondaryTeachersToClassroom($classroom1, [$teacher->id]);

        // Assert $teacher was set correctly
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id])
            ->assertDatabaseHas('classrooms', ['id' => $classroom2->id, 'owner_id' => $teacher->id])
            ->assertDatabaseHas('classroom_secondary_teacher', [
                'classroom_id' => $classroom1->id,
                'teacher_id' => $teacher->id,
            ]);

        // Call the delete method.
        $this->teacherService->delete($teacher);

        // Assert that the teacher was soft-deleted.
        $this->assertSoftDeleted('teachers', ['id' => $teacher->id,]);

        // Assert that $teacher was removed from the secondary teachers list.
        $this->assertDatabaseMissing('classroom_secondary_teacher', [
            'classroom_id' => $classroom1->id,
            'teacher_id' => $teacher->id,
        ]);

        // Assert that $teacher was removed from the owner of $classroom2.
        $this->assertDatabaseHas('classrooms', ['id' => $classroom2->id, 'owner_id' => null]);
    }
}
