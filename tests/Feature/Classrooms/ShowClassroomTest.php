<?php

namespace Tests\Feature\Classrooms;

use App\Http\Controllers\Api\V1\ClassroomController;
use Tests\TestCase;

/**
 * @see ClassroomController::show()
 */
class ShowClassroomTest extends TestCase
{
    public function test_a_guest_cannot_get_details_of_a_classroom()
    {
        $teacher = $this->fakeTeacher();
        $classroom = $this->fakeClassroom($teacher);

        $this->assertGuest();

        $response = $this->getJson(route('api.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 401 “Unauthorized” status code.
        $response->assertUnauthorized();
    }

    public function test_a_teacher_in_an_unsubscribed_school_cannot_get_details_of_a_classroom(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $teacher = $this->fakeAdminTeacher($school);
            $classroom = $this->fakeClassroom($teacher);
        }

        $this->actingAsTeacher($teacher);

        $response = $this->getJson(route('api.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 402 “Payment Required” status code.
        $response->assertUnsubscribed();
    }

    public function test_an_admin_teacher_can_get_details_of_a_classroom_in_their_school(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

            $classroom = $this->fakeClassroom($nonAdminTeacher);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk()->assertJsonSuccessful();

        // Assert that the response returns the correct classroom.
        $response->assertJsonFragment(['id' => $classroom->id]);
    }

    public function test_an_admin_teacher_is_unauthorized_to_get_details_of_a_classroom_in_another_school(): void
    {
        {
            $school1 = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school1);
            $adminTeacher = $this->fakeAdminTeacher($school1);

            $school2 = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school2);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school2);
            $classroom = $this->fakeClassroom($nonAdminTeacher);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    public function test_a_non_admin_teacher_can_get_details_of_an_owned_classroom()
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);

            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

            $classroom = $this->fakeClassroom($nonAdminTeacher);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk()->assertJsonSuccessful();

        // Assert that the response returns the correct classroom details.
        $response->assertJsonFragment(['id' => $classroom->id]);
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_get_details_of_a_classroom_that_they_do_not_own()
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);

            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
            $adminTeacher = $this->fakeAdminTeacher($school);

            $classroom = $this->fakeClassroom($adminTeacher);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    public function test_it_returns_expected_details_of_the_classroom()
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $teacher = $this->fakeTeacher($school);
            $classroom = $this->fakeClassroom($teacher);
        }

        $this->actingAsTeacher($teacher);

        $response = $this->getJson(route('api.v1.classrooms.show', $classroom->id));

        // Assert that the response has the expected value of each attribute.
        $response->assertJsonStructure([
            'data' => [
                'id',
                'school_id',
                'type',
                'name',
                'owner_id',
                'pass_grade',
                'attempts',
                'owner' => ['id', 'first_name', 'last_name', 'title'],
                'secondary_teachers' => [
                    '*' => ['id', 'first_name', 'last_name', 'title']
                ],
                'custom_groups' => [
                    '*' => ['id', 'name', 'pass_grade', 'attempts']
                ],
            ],
        ]);
    }
}
