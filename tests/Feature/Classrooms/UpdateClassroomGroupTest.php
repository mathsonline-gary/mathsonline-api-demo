<?php

namespace Feature\Classrooms;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateClassroomGroupTest extends TestCase
{
    use RefreshDatabase,
        withFaker;

    /**
     * The payload to use for the request.
     *
     * @var array
     */
    protected array $payload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payload = [
            'name' => fake()->name(),
            'pass_grade' => fake()->numberBetween(0, 100),
            'attempts' => fake()->numberBetween(1, 10),
        ];
    }

    public function test_admin_teachers_can_update_classroom_group_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($teacher);

        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(
            route('api.teachers.v1.classrooms.groups.update', [
                'classroom' => $classroom->id,
                'classroomGroup' => $customClassroomGroup->id,
            ]), $this->payload);

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $customClassroomGroup->id,
            'classroom_id' => $classroom->id,
            'name' => $this->payload['name'],
            'pass_grade' => $this->payload['pass_grade'],
            'attempts' => $this->payload['attempts'],
            'is_default' => false,
        ]);
    }

    public function test_admin_teachers_are_unauthorized_to_update_groups_of_classrooms_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $classroom = $this->fakeClassroom($teacher);

        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(
            route('api.teachers.v1.classrooms.groups.update', [
                'classroom' => $classroom->id,
                'classroomGroup' => $customClassroomGroup->id,
            ]), $this->payload);

        $response->assertForbidden();
    }

    public function test_non_admin_teachers_can_update_groups_of_classrooms_that_they_own(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(
            route('api.teachers.v1.classrooms.groups.update', [
                'classroom' => $classroom->id,
                'classroomGroup' => $customClassroomGroup->id,
            ]), $this->payload);

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $customClassroomGroup->id,
            'classroom_id' => $classroom->id,
            'name' => $this->payload['name'],
            'pass_grade' => $this->payload['pass_grade'],
            'attempts' => $this->payload['attempts'],
            'is_default' => false,
        ]);
    }

    public function test_non_admin_teachers_are_unauthorized_to_update_groups_of_classrooms_that_they_do_not_own(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($teacher);

        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(
            route('api.teachers.v1.classrooms.groups.update', [
                'classroom' => $classroom->id,
                'classroomGroup' => $customClassroomGroup->id,
            ]), $this->payload);

        $response->assertForbidden();
    }

    public function test_it_responses_not_found_when_the_classroom_group_does_not_belong_to_the_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($adminTeacher);
        $classroom2 = $this->fakeClassroom($adminTeacher);

        $customClassroomGroup = $this->fakeCustomClassroomGroup($classroom2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(
            route('api.teachers.v1.classrooms.groups.update', [
                'classroom' => $classroom1->id,
                'classroomGroup' => $customClassroomGroup->id,
            ]), $this->payload);

        $response->assertNotFound();
    }
}
