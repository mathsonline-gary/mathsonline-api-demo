<?php

namespace Tests\Feature\TeacherApis\Teachers;

use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SchoolHelpers;
use Tests\Traits\TeacherHelpers;

class TeacherDeletionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     *
     * @see
     */
    public function test_teacher_admins_can_delete_teachers_in_the_same_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $teacherAdmin = $this->createTeacherAdmin($school);

        $teacher = $this->createNonAdminTeacher($school);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->deleteJson(route('api.teachers.v1.teachers.destroy', ['teacher' => $teacher]));

        $response->assertStatus(200);
    }
}
