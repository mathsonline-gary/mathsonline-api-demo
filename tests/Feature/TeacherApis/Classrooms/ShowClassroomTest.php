<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use App\Http\Controllers\Api\Teachers\V1\ClassroomController;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see ClassroomController::show()
 */
class ShowClassroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_teachers_can_get_details_of_classrooms_in_the_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.teachers.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk();

        // Assert that the response returns the correct classroom details.
        $response->assertJsonFragment(['id' => $classroom->id]);
    }

    public function test_admin_teachers_are_unauthorised_to_get_details_of_classrooms_in_other_schools(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school2);
        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.teachers.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 404 “Not Found” status code.
        $response->assertNotFound();
    }

    public function test_non_admin_teachers_can_get_details_of_owned_classrooms()
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk();

        // Assert that the response returns the correct classroom details.
        $response->assertJsonFragment(['id' => $classroom->id]);
    }

    public function test_non_admin_teachers_are_unauthorised_to_get_details_of_classrooms_that_they_do_not_own()
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $adminTeacher = $this->fakeAdminTeacher($school);
        $classroom = $this->fakeClassroom($adminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 404 “Not Found” status code.
        $response->assertNotFound();
    }
}
