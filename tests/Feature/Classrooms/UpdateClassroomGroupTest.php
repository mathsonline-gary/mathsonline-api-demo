<?php

namespace Tests\Feature\Classrooms;

use Tests\TestCase;

class UpdateClassroomGroupTest extends TestCase
{
    /**
     * The route name for the api endpoint.
     *
     * @var string
     */
    protected string $routeName = 'api.v1.classrooms.groups.update';

    /**
     * The payload for the test.
     *
     * @var array
     */
    protected array $payload = [
        'adds' => [],
        'removes' => [],
        'updates' => [],
    ];

    public function test_a_guest_cannot_update_classroom_groups(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $teacher = $this->fakeTeacher($school);

            $classroom = $this->fakeClassroom($teacher);
        }

        $this->assertGuest();

        $response = $this->putJson(
            route($this->routeName, $classroom),
            $this->payload,
        );

        $response->assertUnauthorized();
    }

    public function test_a_teacher_in_an_unsubscribed_school_cannot_update_classroom_groups(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $teacher = $this->fakeTeacher($school);

            $classroom = $this->fakeClassroom($teacher);
        }

        $this->actingAsTeacher($teacher);

        $response = $this->putJson(
            route($this->routeName, $classroom),
            $this->payload,
        );

        $response->assertUnsubscribed();
    }

    public function test_an_admin_teacher_can_update_classroom_groups_of_a_classroom_in_the_same_school()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $classroom = $this->fakeClassroom($adminTeacher);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(
            route($this->routeName, $classroom),
            $this->payload,
        );

        $response->assertOk()
            ->assertJsonSuccessful();
    }

    public function test_an_admin_teacher_cannot_update_classroom_groups_of_a_classroom_in_another_school()
    {
        {
            $school1 = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school1);

            $adminTeacher1 = $this->fakeAdminTeacher($school1);

            $classroom = $this->fakeClassroom($adminTeacher1);

            $school2 = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school2);

            $adminTeacher2 = $this->fakeAdminTeacher($school2);

            // Assert that the admin teacher is not in the same school as the classroom.
            $this->assertNotEquals($adminTeacher2->school_id, $classroom->school_id);
        }

        $this->actingAsTeacher($adminTeacher2);

        $response = $this->putJson(
            route($this->routeName, $classroom),
            $this->payload,
        );

        $response->assertForbidden();
    }

    public function test_a_non_admin_teacher_can_update_classroom_groups_of_a_classroom_owned_by_them()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

            $classroom = $this->fakeClassroom($nonAdminTeacher);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(
            route($this->routeName, $classroom),
            $this->payload,
        );

        $response->assertOk()
            ->assertJsonSuccessful();
    }

    public function test_a_non_admin_teacher_cannot_update_classroom_groups_of_a_classroom_that_is_not_owned_by_them()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

            $classroom = $this->fakeClassroom($adminTeacher);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(
            route($this->routeName, $classroom),
            $this->payload,
        );

        $response->assertForbidden();
    }

    public function test_it_updates_classroom_groups()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $classroom = $this->fakeClassroom($adminTeacher);

            $group = $this->fakeCustomClassroomGroup($classroom);

            // Set the payload to update a new group.
            $updatedGroup = [
                'id' => $group->id,
                'name' => 'New Group Name',
                'pass_grade' => fake()->numberBetween(0, 100),
                'attempts' => fake()->numberBetween(1, 10),
            ];

            $this->payload['updates'] = [$updatedGroup];
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(
            route($this->routeName, $classroom),
            $this->payload,
        );

        $response->assertOk()
            ->assertJsonSuccessful();

        // Assert that the classroom groups have updated successfully.
        $this->assertDatabaseCount('classroom_groups', 2);
        $this->assertDatabaseHas('classroom_groups', [
            'id' => $updatedGroup['id'],
            'classroom_id' => $classroom->id,
            'name' => $updatedGroup['name'],
            'pass_grade' => $updatedGroup['pass_grade'],
            'attempts' => $updatedGroup['attempts'],
            'is_default' => false,
        ]);
    }

    public function test_it_adds_classroom_groups()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $classroom = $this->fakeClassroom($adminTeacher);

            // Set the payload to add a new group.
            $newGroup = [
                'name' => 'New Group',
                'pass_grade' => fake()->numberBetween(0, 100),
                'attempts' => fake()->numberBetween(1, 10),
            ];

            $this->payload['adds'] = [$newGroup];
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(
            route($this->routeName, $classroom),
            $this->payload,
        );

        $response->assertOk()
            ->assertJsonSuccessful();

        // Assert that the classroom groups have added successfully.
        $this->assertDatabaseCount('classroom_groups', 2);
        $this->assertDatabaseHas('classroom_groups', [
            'classroom_id' => $classroom->id,
            'name' => $newGroup['name'],
            'pass_grade' => $newGroup['pass_grade'],
            'attempts' => $newGroup['attempts'],
            'is_default' => false,
        ]);
    }

    public function test_it_soft_removes_classroom_groups()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $adminTeacher = $this->fakeAdminTeacher($school);

            $classroom = $this->fakeClassroom($adminTeacher);

            $group1 = $this->fakeCustomClassroomGroup($classroom);
            $group2 = $this->fakeCustomClassroomGroup($classroom);

            // Add students to the groups.
            $student1 = $this->fakeStudent($classroom->school);
            $student2 = $this->fakeStudent($classroom->school);
            $group1->students()->attach($student1);
            $group1->students()->attach($student2);
            $group2->students()->attach($student1);

            // Assert that the students have added to the groups successfully.
            $this->assertDatabaseCount('classroom_group_student', 3);

            // Set the payload to remove groups.
            $this->payload['removes'] = [$group1->id, $group2->id];
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(
            route($this->routeName, $classroom),
            $this->payload,
        );

        $response->assertOk()
            ->assertJsonSuccessful();

        // Assert that the classroom groups have removed successfully.
        $this->assertDatabaseCount('classroom_groups', 3);
        $this->assertSoftDeleted('classroom_groups', [
            'id' => $group1->id,
            'classroom_id' => $classroom->id,
        ]);
        $this->assertSoftDeleted('classroom_groups', [
            'id' => $group2->id,
            'classroom_id' => $classroom->id,
        ]);

        // Assert that the students of the removed groups have been moved to the default group.
        $this->assertDatabaseCount('classroom_group_student', 2);
        $students = $classroom->refresh()->defaultClassroomGroup->students;
        $this->assertCount(2, $students);
        $this->assertTrue($students->contains($student1));
        $this->assertTrue($students->contains($student2));
    }
}
