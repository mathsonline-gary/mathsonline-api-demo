<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexClassroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_teachers_can_get_all_classrooms_in_his_school()
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

    public function test_non_admin_teachers_can_only_get_classrooms_owned_by_himself()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $teacher1 = $this->createNonAdminTeacher($school);
        $classroom1 = $this->createClassroom($teacher1);

        $teacher2 = $this->createNonAdminTeacher($school);
        $classroom2 = $this->createClassroom($teacher2);

        $this->actingAsTeacher($teacher1);

        $response = $this->getJson(route('api.teachers.v1.classrooms.index'));

        // Assert a successful response.
        $response->assertOk();
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

        // Assert that it only contains the classroom owned by the non-admin teacher.
        $response->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $classroom1->id])
            ->assertJsonMissing(['id' => $classroom2->id]);
    }
}
