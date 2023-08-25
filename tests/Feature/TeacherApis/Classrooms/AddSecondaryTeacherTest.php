<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddSecondaryTeacherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run MarketSeeder before each test.
     *
     * @var string
     */
    protected string $seeder = MarketSeeder::class;

    public function test_admin_teachers_can_add_secondary_teachers_into_classrooms_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.teachers.v1.classrooms.secondary-teachers.store', [
            'classroom' => $classroom->id,
            'teacher' => $nonAdminTeacher->id,
        ]));

        // Assert that the request is successful.
        $response->assertCreated();
    }

    public function test_admin_teachers_cannot_add_secondary_teachers_into_classrooms_in_other_schools(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school2);
        $classroom = $this->fakeClassroom($nonAdminTeacher2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.teachers.v1.classrooms.secondary-teachers.store', [
            'classroom' => $classroom->id,
            'teacher' => $nonAdminTeacher1->id,
        ]));

        // Assert that the request is unauthorized.
        $response->assertNotFound();
    }

    public function test_admin_teachers_cannot_add_teachers_in_other_schools_as_secondary_teachers(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $classroom = $this->fakeClassroom($adminTeacher);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.teachers.v1.classrooms.secondary-teachers.store', [
            'classroom' => $classroom->id,
            'teacher' => $nonAdminTeacher->id,
        ]));

        // Assert that the response status code is 404.
        $response->assertNotFound();
    }

    public function test_admin_teachers_cannot_add_the_teacher_into_the_classroom_if_the_teacher_is_already_the_secondary_teacher_of_the_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        // Add the teacher as the secondary teacher of the classroom.
        $this->attachSecondaryTeachersToClassroom($classroom, [$nonAdminTeacher->id]);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.teachers.v1.classrooms.secondary-teachers.store', [
            'classroom' => $classroom->id,
            'teacher' => $nonAdminTeacher->id,
        ]));

        // Assert that the response status code is 422.
        $response->assertStatus(422);
    }

    public function test_admin_teachers_cannot_add_the_teacher_into_the_classroom_if_the_teacher_is_the_owner_of_the_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.teachers.v1.classrooms.secondary-teachers.store', [
            'classroom' => $classroom->id,
            'teacher' => $adminTeacher->id,
        ]));

        // Assert that the response status code is 422.
        $response->assertStatus(422);
    }

    public function test_non_admin_teachers_are_unauthorized_to_add_secondary_teachers(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher1);

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->postJson(route('api.teachers.v1.classrooms.secondary-teachers.store', [
            'classroom' => $classroom->id,
            'teacher' => $nonAdminTeacher2->id,
        ]));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }
}
