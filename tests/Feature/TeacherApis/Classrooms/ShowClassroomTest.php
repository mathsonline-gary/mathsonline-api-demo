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
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $adminTeacher = $this->createAdminTeacher($school);
        $nonAdminTeacher = $this->createNonAdminTeacher($school);

        $classroom = $this->createClassroom($nonAdminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.teachers.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk();

        // Assert that the response returns the correct classroom details.
        $response->assertJsonFragment(['id' => $classroom->id]);
    }

    public function test_admin_teachers_are_unauthorised_to_get_details_of_classrooms_in_other_schools(): void
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->createTraditionalSchool();
        $adminTeacher = $this->createAdminTeacher($school1);

        $school2 = $this->createTraditionalSchool();
        $nonAdminTeacher = $this->createNonAdminTeacher($school2);
        $classroom = $this->createClassroom($nonAdminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.teachers.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 404 “Not Found” status code.
        $response->assertNotFound();
    }

    public function test_non_admin_teachers_can_get_details_of_owned_classrooms()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $nonAdminTeacher = $this->createNonAdminTeacher($school);
        $classroom = $this->createClassroom($nonAdminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 200 “OK” status code.
        $response->assertOk();

        // Assert that the response returns the correct classroom details.
        $response->assertJsonFragment(['id' => $classroom->id]);
    }

    public function test_non_admin_teachers_are_unauthorised_to_get_details_of_classrooms_that_they_do_not_own()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $nonAdminTeacher = $this->createNonAdminTeacher($school);
        $adminTeacher = $this->createAdminTeacher($school);
        $classroom = $this->createClassroom($adminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.classrooms.show', $classroom->id));

        // Assert that the response has a 404 “Not Found” status code.
        $response->assertNotFound();
    }
}
