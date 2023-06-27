<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexClassroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_teachers_can_get_the_list_of_classrooms_of_his_school()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $adminTeacher = $this->createAdminTeacher($school);
        $classrooms = $this->createClassroom($adminTeacher, 10);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.teachers.v1.classrooms.index'));

        // Assertions
        $response->assertOk();
        $response->assertJsonCount($classrooms->count(), 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'school_id',
                    'owner_id',
                    'type',
                    'name',
                    'pass_grade',
                ],
            ]
        ]);
    }

    public function test_non_admin_teachers_are_unauthorised_to_get_the_list_of_classrooms()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $nonAdminTeacher = $this->createNonAdminTeacher($school);
        $classrooms = $this->createClassroom($nonAdminTeacher, 10);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.classrooms.index'));

        // Assertions
        $response->assertForbidden();
    }

    public function test_admin_teachers_can_fuzzy_search_classrooms_of_his_school()
    {
    }
}
