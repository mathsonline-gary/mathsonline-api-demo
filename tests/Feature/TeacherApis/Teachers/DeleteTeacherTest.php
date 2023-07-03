<?php

namespace Tests\Feature\TeacherApis\Teachers;

use App\Events\Teachers\TeacherDeleted;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Test teacher deletion endpoint for teachers.
 *
 * @see /routes/api/api-teachers.php
 * @see TeacherController::destroy()
 */
class DeleteTeacherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function test_teacher_admins_can_delete_teachers_in_the_same_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $teacherAdmin = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($teacherAdmin);
        $classroom2 = $this->fakeClassroom($teacherAdmin);

        $this->attachSecondaryTeachers($classroom1, [$teacher->id]);
        $this->attachSecondaryTeachers($classroom2, [$teacher->id]);

        // Assert that $teacher is in the database
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        // Assert that classrooms were created
        $this->assertDatabaseHas('classrooms', ['id' => $classroom1->id])
            ->assertDatabaseHas('classrooms', ['id' => $classroom2->id]);

        // Assert $teacher was added as a secondary teacher of $classroom1 and $classroom2
        $this->assertDatabaseHas('classroom_secondary_teacher', [
            'classroom_id' => $classroom1->id,
            'teacher_id' => $teacher->id,
        ])->assertDatabaseHas('classroom_secondary_teacher', [
            'classroom_id' => $classroom2->id,
            'teacher_id' => $teacher->id,
        ])->assertTrue($teacher->isSecondaryTeacher());

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', $teacher));

        // Assert that the response returns no content
        $response->assertNoContent();

        // Assert that $teacher was removed from database
        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);

        // Assert that $teacher was removed from the secondary teachers list
        $this->assertDatabaseMissing('classroom_secondary_teacher', [
            'classroom_id' => $classroom1->id,
            'teacher_id' => $teacher->id,
        ])->assertDatabaseMissing('classroom_secondary_teacher', [
            'classroom_id' => $classroom2->id,
            'teacher_id' => $teacher->id,
        ]);

        // Assert that TeacherDeleted event was dispatched.
        Event::assertDispatched(TeacherDeleted::class, function ($event) use ($teacherAdmin, $teacher) {
            return $event->actor->id === $teacherAdmin->id &&
                $event->teacher->id === $teacher->id;
        });
    }

    public function test_teacher_admins_are_unauthorised_to_delete_teachers_in_another_school()
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $teacherAdmin = $this->fakeAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        // Assert that $teacher is in the database
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', $teacher));

        // Assert that the request is unauthorised
        $response->assertForbidden();

        // Assert that $teacher is not deleted
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
    }

    public function test_teacher_admins_are_unauthorised_to_delete_teachers_who_own_classrooms()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $teacherAdmin = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($teacher);

        // Assert that $teacher is in the database
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        // Assert that $teacher owns $classroom
        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'owner_id' => $teacher->id,
        ])->assertTrue($teacher->isClassroomOwner());

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', $teacher));

        // Assert that the request is unauthorised
        $response->assertForbidden();

        // Assert that $teacher is not deleted
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
    }

    public function test_non_admin_teachers_are_unauthorised_to_delete_teachers()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        // Assert that $teacher is in the database
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', $teacher));

        // Assert that the request is unauthorised
        $response->assertForbidden();

        // Assert that $teacher is not deleted
        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
    }
}
