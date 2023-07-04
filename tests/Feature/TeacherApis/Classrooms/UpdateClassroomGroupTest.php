<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateClassroomGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_teachers_can_update_groups_of_classrooms_in_the_same_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($teacher);

        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom, 1, [
            'name' => 'Old Group Name',
            'pass_grade' => 60,
        ]);

        $this->actingAsTeacher($adminTeacher);

        $payload = [
            'name' => 'New Group Name',
            'pass_grade' => 80,
        ];

        $response = $this->putJson(
            route('api.teachers.v1.classrooms.groups.update', [
                'classroom' => $classroom->id,
                'classroomGroup' => $customClassroomGroup->id,
            ]), $payload);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'id' => $customClassroomGroup->id,
            'classroom_id' => $classroom->id,
            'name' => $payload['name'],
            'pass_grade' => $payload['pass_grade'],
            'is_default' => false,
        ]);
    }

    public function test_admin_teachers_are_unauthorized_to_update_groups_of_classrooms_in_another_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $classroom = $this->fakeClassroom($teacher);

        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom, 1, [
            'name' => 'Old Group Name',
            'pass_grade' => 60,
        ]);

        $this->actingAsTeacher($adminTeacher);

        $payload = [
            'name' => 'New Group Name',
            'pass_grade' => 80,
        ];

        $response = $this->putJson(
            route('api.teachers.v1.classrooms.groups.update', [
                'classroom' => $classroom->id,
                'classroomGroup' => $customClassroomGroup->id,
            ]), $payload);

        $response->assertForbidden();
    }

    public function test_non_admin_teachers_can_update_groups_of_classrooms_that_they_own(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom, 1, [
            'name' => 'Old Group Name',
            'pass_grade' => 60,
        ]);

        $this->actingAsTeacher($nonAdminTeacher);

        $payload = [
            'name' => 'New Group Name',
            'pass_grade' => 80,
        ];

        $response = $this->putJson(
            route('api.teachers.v1.classrooms.groups.update', [
                'classroom' => $classroom->id,
                'classroomGroup' => $customClassroomGroup->id,
            ]), $payload);

        $response->assertSuccessful();
        $response->assertJsonFragment([
            'id' => $customClassroomGroup->id,
            'classroom_id' => $classroom->id,
            'name' => $payload['name'],
            'pass_grade' => $payload['pass_grade'],
            'is_default' => false,
        ]);
    }

    public function test_non_admin_teachers_are_unauthorized_to_update_groups_of_classrooms_that_they_do_not_own(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($teacher);

        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom, 1, [
            'name' => 'Old Group Name',
            'pass_grade' => 60,
        ]);

        $this->actingAsTeacher($nonAdminTeacher);

        $payload = [
            'name' => 'New Group Name',
            'pass_grade' => 80,
        ];

        $response = $this->putJson(
            route('api.teachers.v1.classrooms.groups.update', [
                'classroom' => $classroom->id,
                'classroomGroup' => $customClassroomGroup->id,
            ]), $payload);

        $response->assertForbidden();
    }
}
