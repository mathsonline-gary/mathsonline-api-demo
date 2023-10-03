<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use App\Http\Controllers\Api\V1\ClassroomGroupController;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @see ClassroomGroupController::store()
 */
class CreateClassroomGroupTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

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
            'name' => fake()->name,
            'pass_grade' => fake()->numberBetween(0, 100),
            'attempts' => fake()->numberBetween(1, 10),
        ];
    }

    public function test_admin_teachers_can_add_custom_classroom_groups_to_classrooms_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        // Initialize 1 custom classroom group of the classroom.
        $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.teachers.v1.classrooms.groups.store', $classroom), $this->payload);

        // Assert that the response is created.
        $response->assertCreated();

        // Assert that the response contains the new custom classroom group.
        $response->assertJsonFragment([
            'classroom_id' => $classroom->id,
            'name' => $this->payload['name'],
            'pass_grade' => $this->payload['pass_grade'],
            'is_default' => false,
        ]);
    }

    public function test_admin_teachers_are_unauthorized_to_add_custom_classroom_groups_to_classrooms_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher1 = $this->fakeAdminTeacher($school1);
        $adminTeacher2 = $this->fakeAdminTeacher($school2);

        $classroom = $this->fakeClassroom($adminTeacher2);

        // Initialize 1 custom classroom group of the classroom.
        $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($adminTeacher1);

        $response = $this->postJson(route('api.teachers.v1.classrooms.groups.store', $classroom), $this->payload);

        // Assert that the response is forbidden.
        $response->assertForbidden();
    }

    public function test_admin_teachers_cannot_add_custom_classroom_groups_to_classrooms_when_the_max_limit_has_been_reached(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $classroom = $this->fakeClassroom($adminTeacher);

        // Max up custom classroom groups of the classroom.
        $this->fakeCustomClassroomGroup($classroom, Classroom::MAX_CUSTOM_GROUP_COUNT);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.teachers.v1.classrooms.groups.store', $classroom), $this->payload);

        // Assert that the response is conflict.
        $response->assertConflict();
    }

    public function test_non_admin_teachers_can_add_custom_classroom_groups_to_classrooms_that_they_own(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        // Initialize 1 custom classroom group of the classroom.
        $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->postJson(route('api.teachers.v1.classrooms.groups.store', $classroom), $this->payload);

        // Assert that the response is created.
        $response->assertCreated();

        // Assert that the response contains the new custom classroom group.
        $response->assertJsonFragment([
            'classroom_id' => $classroom->id,
            'name' => $this->payload['name'],
            'pass_grade' => $this->payload['pass_grade'],
            'attempts' => $this->payload['attempts'],
            'is_default' => false,
        ]);
    }

    public function test_non_admin_teachers_are_unauthorized_to_add_custom_classroom_groups_to_classroom_that_they_do_not_own(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher2);

        // Initialize 1 custom classroom group of the classroom.
        $this->fakeCustomClassroomGroup($classroom);

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->postJson(route('api.teachers.v1.classrooms.groups.store', $classroom), $this->payload);

        // Assert that the response is forbidden.
        $response->assertForbidden();
    }

    public function test_non_admin_teachers_cannot_add_classrooms_groups_to_classrooms_when_the_max_limit_has_been_reached()
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher);

        // Max up custom classroom groups of the classroom.
        $this->fakeCustomClassroomGroup($classroom, Classroom::MAX_CUSTOM_GROUP_COUNT);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->postJson(route('api.teachers.v1.classrooms.groups.store', $classroom), $this->payload);

        // Assert that the response is conflict.
        $response->assertConflict();
    }
}
