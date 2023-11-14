<?php

namespace Tests\Feature\Classrooms;

use App\Models\Classroom;
use App\Models\ClassroomGroup;
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
     * The classroom for the test.
     *
     * @var Classroom
     */
    protected Classroom $classroom;

    /**
     * The classroom groups for the test.
     *
     * @var ClassroomGroup
     */
    protected ClassroomGroup $group1;

    /**
     * The classroom groups for the test.
     *
     * @var ClassroomGroup
     */
    protected ClassroomGroup $group2;

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

    protected function setUp(): void
    {
        parent::setUp();

        $teacher = $this->fakeTeacher();
        $this->classroom = $this->fakeClassroom($teacher);
        $this->group1 = $this->fakeCustomClassroomGroup($this->classroom);
        $this->group2 = $this->fakeCustomClassroomGroup($this->classroom);

        // Assert that the classroom has created successfully.
        $this->assertDatabaseHas('classrooms', [
            'id' => $this->classroom->id,
            'name' => $this->classroom->name,
            'owner_id' => $teacher->id,
        ]);

        // Assert that the classroom groups have created successfully.
        $this->assertDatabaseCount('classroom_groups', 3);
        $this->assertDatabaseHas('classroom_groups', [
            'id' => $this->group1->id,
            'name' => $this->group1->name,
            'classroom_id' => $this->classroom->id,
        ]);
        $this->assertDatabaseHas('classroom_groups', [
            'id' => $this->group2->id,
            'name' => $this->group2->name,
            'classroom_id' => $this->classroom->id,
        ]);

        // Assert that the classroom has correct groups.
        $this->assertCount(2, $this->classroom->customClassroomGroups);
        $this->assertTrue($this->classroom->customClassroomGroups->contains($this->group1));
        $this->assertTrue($this->classroom->customClassroomGroups->contains($this->group2));
    }

    /**
     * Authorization test.
     */
    public function test_a_guest_cannot_update_classroom_groups(): void
    {
        $this->assertGuest();

        $response = $this->putJson(
            route($this->routeName, $this->classroom),
            $this->payload,
        );

        $response->assertUnauthorized();
    }

    /**
     * Authorization test.
     */
    public function test_an_admin_teacher_can_update_classroom_groups_of_a_classroom_in_the_same_school()
    {
        $adminTeacher = $this->fakeAdminTeacher($this->classroom->school);

        // Assert that the admin teacher is in the same school as the classroom.
        $this->assertEquals($adminTeacher->school_id, $this->classroom->school_id);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(
            route($this->routeName, $this->classroom),
            $this->payload,
        );

        $response->assertOk()
            ->assertJsonFragment(['success' => true]);
    }

    /**
     * Authorization test.
     */
    public function test_an_admin_teacher_cannot_update_classroom_groups_of_a_classroom_in_another_school()
    {
        $adminTeacher = $this->fakeAdminTeacher();

        // Assert that the admin teacher is not in the same school as the classroom.
        $this->assertNotEquals($adminTeacher->school_id, $this->classroom->school_id);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(
            route($this->routeName, $this->classroom),
            $this->payload,
        );

        $response->assertForbidden();
    }

    /**
     * Authorization test.
     */
    public function test_a_non_admin_teacher_can_update_classroom_groups_of_a_classroom_owned_by_them()
    {
        $nonAdminTeacher = $this->fakeNonAdminTeacher($this->classroom->school);
        $this->classroom->update(['owner_id' => $nonAdminTeacher->id]);

        // Assert that the non-admin teacher is the owner of the classroom.
        $this->assertEquals($nonAdminTeacher->id, $this->classroom->owner_id);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(
            route($this->routeName, $this->classroom),
            $this->payload,
        );

        $response->assertOk()
            ->assertJsonFragment(['success' => true]);
    }

    /**
     * Authorization test.
     */
    public function test_a_non_admin_teacher_cannot_update_classroom_groups_of_a_classroom_that_is_not_owned_by_them()
    {
        $nonAdminTeacher = $this->fakeNonAdminTeacher($this->classroom->school);

        // Assert that the non-admin teacher is not the owner of the classroom.
        $this->assertNotEquals($nonAdminTeacher->id, $this->classroom->owner_id);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(
            route($this->routeName, $this->classroom),
            $this->payload,
        );

        $response->assertForbidden();
    }

    /**
     * Operation test.
     */
    public function test_it_updates_classroom_groups()
    {
        $adminTeacher = $this->fakeAdminTeacher($this->classroom->school);

        $this->actingAsTeacher($adminTeacher);

        // Set the payload to add a new group.
        $updatedGroup = [
            'id' => $this->group1->id,
            'name' => 'New Group Name',
            'pass_grade' => fake()->numberBetween(0, 100),
            'attempts' => fake()->numberBetween(1, 10),
        ];
        $this->payload['updates'] = [$updatedGroup];

        $response = $this->putJson(
            route($this->routeName, $this->classroom),
            $this->payload,
        );

        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        // Assert that the classroom groups have updated successfully.
        $this->assertDatabaseCount('classroom_groups', 3);
        $this->assertDatabaseHas('classroom_groups', [
            'id' => $updatedGroup['id'],
            'classroom_id' => $this->classroom->id,
            'name' => $updatedGroup['name'],
            'pass_grade' => $updatedGroup['pass_grade'],
            'attempts' => $updatedGroup['attempts'],
            'is_default' => false,
        ]);
    }

    /**
     * Operation test.
     */
    public function test_it_adds_classroom_groups()
    {
        $adminTeacher = $this->fakeAdminTeacher($this->classroom->school);

        $this->actingAsTeacher($adminTeacher);

        // Set the payload to add a new group.
        $newGroup = [
            'name' => 'New Group',
            'pass_grade' => fake()->numberBetween(0, 100),
            'attempts' => fake()->numberBetween(1, 10),
        ];
        $this->payload['adds'] = [$newGroup];

        $response = $this->putJson(
            route($this->routeName, $this->classroom),
            $this->payload,
        );

        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        // Assert that the classroom groups have added successfully.
        $this->assertDatabaseCount('classroom_groups', 4);
        $this->assertDatabaseHas('classroom_groups', [
            'classroom_id' => $this->classroom->id,
            'name' => $newGroup['name'],
            'pass_grade' => $newGroup['pass_grade'],
            'attempts' => $newGroup['attempts'],
            'is_default' => false,
        ]);
    }

    /**
     * Operation test.
     */
    public function test_it_soft_removes_classroom_groups()
    {
        $adminTeacher = $this->fakeAdminTeacher($this->classroom->school);

        // Add students to the groups.
        $student1 = $this->fakeStudent($this->classroom->school);
        $student2 = $this->fakeStudent($this->classroom->school);
        $this->group1->students()->attach($student1);
        $this->group1->students()->attach($student2);
        $this->group2->students()->attach($student1);

        // Assert that the students have added to the groups successfully.
        $this->assertDatabaseCount('classroom_group_student', 3);

        $this->actingAsTeacher($adminTeacher);

        // Set the payload to remove groups.
        $this->payload['removes'] = [$this->group1->id, $this->group2->id];

        $response = $this->putJson(
            route($this->routeName, $this->classroom),
            $this->payload,
        );

        $response->assertOk()
            ->assertJsonFragment(['success' => true]);

        // Assert that the classroom groups have removed successfully.
        $this->assertDatabaseCount('classroom_groups', 3);
        $this->assertSoftDeleted('classroom_groups', [
            'id' => $this->group1->id,
            'classroom_id' => $this->classroom->id,
        ]);
        $this->assertSoftDeleted('classroom_groups', [
            'id' => $this->group2->id,
            'classroom_id' => $this->classroom->id,
        ]);

        // Assert that the students of the removed groups have been moved to the default group.
        $this->assertDatabaseCount('classroom_group_student', 2);
        $students = $this->classroom->refresh()->defaultClassroomGroup->students;
        $this->assertCount(2, $students);
        $this->assertTrue($students->contains($student1));
        $this->assertTrue($students->contains($student2));
    }
}
