<?php

namespace Feature\Classrooms;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddSecondaryTeacherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authorization test.
     */
    public function test_a_guest_cannot_add_a_secondary_teacher()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->assertGuest();

        $response = $this->postJson(
            route('api.v1.classrooms.secondary-teachers.store', ['classroom' => $classroom->id]),
            ['user_id' => $nonAdminTeacher->id]
        );

        // Assert that the request is unauthorized.
        $response->assertUnauthorized();
    }

    /**
     * Authorization test.
     */
    public function test_an_admin_teacher_can_add_a_teacher_as_the_secondary_teacher_of_a_classroom_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(
            route('api.v1.classrooms.secondary-teachers.store', ['classroom' => $classroom->id]),
            ['user_id' => $nonAdminTeacher->user_id]
        );

        // Assert that the request is successful.
        $response->assertCreated()->assertJsonFragment(['success' => true]);
    }

    /**
     * Authorization test.
     */
    public function test_an_admin_teacher_cannot_add_secondary_teachers_into_a_classroom_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school2);
        $classroom = $this->fakeClassroom($nonAdminTeacher2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(
            route('api.v1.classrooms.secondary-teachers.store', ['classroom' => $classroom->id]),
            ['user_id' => $nonAdminTeacher1->user_id]
        );

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    /**
     * Validation test.
     */
    public function test_an_admin_teacher_cannot_add_a_teacher_in_another_school_as_secondary_teacher(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);
        $classroom = $this->fakeClassroom($adminTeacher);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(
            route('api.v1.classrooms.secondary-teachers.store', ['classroom' => $classroom->id]),
            ['user_id' => $nonAdminTeacher->user_id]
        );

        $response->assertUnprocessable();
    }

    /**
     * Validation test.
     */
    public function test_an_admin_teacher_cannot_add_the_teacher_into_the_classroom_if_the_teacher_is_already_the_secondary_teacher_of_the_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        // Add the teacher as the secondary teacher of the classroom.
        $this->attachSecondaryTeachersToClassroom($classroom, [$nonAdminTeacher->id]);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(
            route('api.v1.classrooms.secondary-teachers.store', ['classroom' => $classroom->id]),
            ['user_id' => $nonAdminTeacher->user_id]
        );

        // Assert that the response status code is 422.
        $response->assertUnprocessable();
    }

    /**
     * Validation test.
     */
    public function test_an_admin_teacher_cannot_add_the_teacher_into_the_classroom_if_the_teacher_is_the_owner_of_the_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(
            route('api.v1.classrooms.secondary-teachers.store', ['classroom' => $classroom->id]),
            ['user_id' => $adminTeacher->user_id]
        );

        // Assert that the response status code is 422.
        $response->assertUnprocessable();
    }

    /**
     * Authorization test.
     */
    public function test_a_non_admin_teacher_is_unauthorized_to_add_secondary_teachers(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher1);

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->postJson(
            route('api.v1.classrooms.secondary-teachers.store', ['classroom' => $classroom->id]),
            ['user_id' => $nonAdminTeacher2->user_id]
        );

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }
}
