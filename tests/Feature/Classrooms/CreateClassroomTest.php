<?php

namespace Tests\Feature\Classrooms;

use App\Models\Activity;
use App\Models\Classroom;
use App\Models\ClassroomGroup;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateClassroomTest extends TestCase
{
    use WithFaker;

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
            'mastery_enabled' => fake()->boolean,
            'self_rating_enabled' => fake()->boolean,
        ];
    }

    /**
     * Test that a guest is not authenticated to create a classroom.
     *
     * @return void
     */
    public function test_a_guest_cannot_create_a_classroom()
    {
        $this->assertGuest();

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 401 status code.
        $response->assertUnauthorized();

        // Assert that no classroom was created.
        $this->assertDatabaseCount(Classroom::class, 0);
        $this->assertDatabaseCount(ClassroomGroup::class, 0);
        $this->assertDatabaseCount(Activity::class, 0);
    }

    /**
     * Test that a teacher cannot create a classroom, if his school has no active subscription.
     *
     * @return void
     */
    public function test_a_teacher_in_the_unsubscribed_school_cannot_create_a_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->actingAsTeacher($this->fakeTeacher($school));

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has unsubscription error.
        $response->assertUnsubscribed();

        // Assert that no classroom was created.
        $this->assertDatabaseCount(Classroom::class, 0);
        $this->assertDatabaseCount(ClassroomGroup::class, 0);
        $this->assertDatabaseCount(Activity::class, 0);
    }

    /**
     * Test that an admin teacher can create a classroom of which the owner is a teacher in the same school.
     *
     * @return void
     */
    public function test_an_admin_teacher_can_create_a_classroom_for_a_teacher_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);
        $adminTeacher = $this->fakeAdminTeacher($school);
        $owner = $this->fakeTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $owner->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated()->assertJsonSuccessful();

        // Assert that the classroom was created correctly.
        $this->assertDatabaseCount(Classroom::class, 1);
        $classroom = Classroom::first();
        $this->assertNotSoftDeleted($classroom);
        $this->assertEquals($school->id, $classroom->school_id);
        $this->assertEquals($this->payload['year_id'], $classroom->year_id);
        $this->assertEquals($owner->id, $classroom->owner_id);
        $this->assertEquals(Classroom::TYPE_TRADITIONAL_CLASSROOM, $classroom->type);
        $this->assertEquals($this->payload['name'], $classroom->name);
        $this->assertEquals($this->payload['mastery_enabled'], $classroom->mastery_enabled);
        $this->assertEquals($this->payload['self_rating_enabled'], $classroom->self_rating_enabled);

        // Assert that the default classroom group was created correctly.
        $this->assertDatabaseCount(ClassroomGroup::class, 1);
        $this->assertEquals(1, $classroom->classroomGroups()->count());
        $classroomGroup = ClassroomGroup::first();
        $this->assertEquals($classroomGroup, $classroom->defaultClassroomGroup);
        $this->assertEquals($classroom->id, $classroomGroup->classroom_id);
        $this->assertEquals($classroom->name . ' default group', $classroomGroup->name);
        $this->assertEquals($this->payload['pass_grade'], $classroomGroup->pass_grade);
        $this->assertEquals($this->payload['attempts'], $classroomGroup->attempts);
        $this->assertTrue($classroomGroup->is_default);
        $this->assertNotSoftDeleted($classroomGroup);

        // Assert that the activity was created correctly.
        $this->assertDatabaseCount(Activity::class, 1);
        $this->assertEquals(1, $adminTeacher->user->activities()->count());
        $activity = Activity::first();
        $this->assertEquals($activity, $adminTeacher->user->activities()->first());
        $this->assertEquals($adminTeacher->user->id, $activity->actor_id);
        $this->assertEquals(Activity::TYPE_CREATE_CLASSROOM, $activity->type);
        $this->assertEquals('created classroom: ' . $classroom->name, $activity->description);
        $this->assertEquals($classroom->id, $activity->data['classroom_id']);
    }

    /**
     * Test that an admin teacher cannot create a classroom of which the owner is a teacher in another school.
     *
     * @return void
     */
    public function test_an_admin_teacher_cannot_create_a_classroom_for_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school1);
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school2);
        $anotherTeacher = $this->fakeTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $anotherTeacher->id;
        $this->payload['year_id'] = $school1->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertUnprocessable();

        // Assert that no classroom was created.
        $this->assertDatabaseCount(Classroom::class, 0);
        $this->assertDatabaseCount(ClassroomGroup::class, 0);
        $this->assertDatabaseCount(Activity::class, 0);
    }

    /**
     * Test that an admin teacher can assign secondary teachers to the classroom.
     *
     * @return void
     */
    public function test_an_admin_teacher_can_assign_secondary_teachers()
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);
        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $adminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;
        $this->payload['secondary_teacher_ids'] = [
            $nonAdminTeacher1->id,
            $nonAdminTeacher2->id,
        ];

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated()->assertJsonSuccessful();

        // Assert that the classroom was created correctly.
        $this->assertDatabaseCount(Classroom::class, 1);
        $classroom = Classroom::first();
        $this->assertNotSoftDeleted($classroom);
        $this->assertEquals($school->id, $classroom->school_id);
        $this->assertEquals($this->payload['year_id'], $classroom->year_id);
        $this->assertEquals($adminTeacher->id, $classroom->owner_id);
        $this->assertEquals(Classroom::TYPE_TRADITIONAL_CLASSROOM, $classroom->type);
        $this->assertEquals($this->payload['name'], $classroom->name);
        $this->assertEquals($this->payload['mastery_enabled'], $classroom->mastery_enabled);
        $this->assertEquals($this->payload['self_rating_enabled'], $classroom->self_rating_enabled);

        // Assert that the default classroom group was created correctly.
        $this->assertDatabaseCount(ClassroomGroup::class, 1);
        $this->assertEquals(1, $classroom->classroomGroups()->count());
        $classroomGroup = ClassroomGroup::first();
        $this->assertEquals($classroomGroup, $classroom->defaultClassroomGroup);
        $this->assertEquals($classroom->id, $classroomGroup->classroom_id);
        $this->assertEquals($classroom->name . ' default group', $classroomGroup->name);
        $this->assertEquals($this->payload['pass_grade'], $classroomGroup->pass_grade);
        $this->assertEquals($this->payload['attempts'], $classroomGroup->attempts);
        $this->assertTrue($classroomGroup->is_default);
        $this->assertNotSoftDeleted($classroomGroup);

        // Assert that the activity was created correctly.
        $this->assertDatabaseCount(Activity::class, 1);
        $this->assertEquals(1, $adminTeacher->user->activities()->count());
        $activity = Activity::first();
        $this->assertEquals($activity, $adminTeacher->user->activities()->first());
        $this->assertEquals($adminTeacher->user->id, $activity->actor_id);
        $this->assertEquals(Activity::TYPE_CREATE_CLASSROOM, $activity->type);
        $this->assertEquals('created classroom: ' . $classroom->name, $activity->description);
        $this->assertEquals($classroom->id, $activity->data['classroom_id']);

        // Assert that the secondary teachers were assigned correctly.
        $this->assertDatabaseCount('classroom_secondary_teacher', 2);
        $this->assertEquals(2, $classroom->secondaryTeachers()->count());
    }

    /**
     * Test that an admin teacher can create a classroom of which the owner is himself.
     *
     * @return void
     */
    public function test_a_non_admin_teachers_can_create_a_classroom_for_himself(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated()->assertJsonSuccessful();

        // Assert that the classroom was created correctly.
        $this->assertDatabaseCount(Classroom::class, 1);
        $classroom = Classroom::first();
        $this->assertNotSoftDeleted($classroom);
        $this->assertEquals($school->id, $classroom->school_id);
        $this->assertEquals($this->payload['year_id'], $classroom->year_id);
        $this->assertEquals($nonAdminTeacher->id, $classroom->owner_id);
        $this->assertEquals(Classroom::TYPE_TRADITIONAL_CLASSROOM, $classroom->type);
        $this->assertEquals($this->payload['name'], $classroom->name);
        $this->assertEquals($this->payload['mastery_enabled'], $classroom->mastery_enabled);
        $this->assertEquals($this->payload['self_rating_enabled'], $classroom->self_rating_enabled);

        // Assert that the default classroom group was created correctly.
        $this->assertDatabaseCount(ClassroomGroup::class, 1);
        $this->assertEquals(1, $classroom->classroomGroups()->count());
        $classroomGroup = ClassroomGroup::first();
        $this->assertEquals($classroomGroup, $classroom->defaultClassroomGroup);
        $this->assertEquals($classroom->id, $classroomGroup->classroom_id);
        $this->assertEquals($classroom->name . ' default group', $classroomGroup->name);
        $this->assertEquals($this->payload['pass_grade'], $classroomGroup->pass_grade);
        $this->assertEquals($this->payload['attempts'], $classroomGroup->attempts);
        $this->assertTrue($classroomGroup->is_default);
        $this->assertNotSoftDeleted($classroomGroup);

        // Assert that the activity was created correctly.
        $this->assertDatabaseCount(Activity::class, 1);
        $this->assertEquals(1, $nonAdminTeacher->user->activities()->count());
        $activity = Activity::first();
        $this->assertEquals($activity, $nonAdminTeacher->user->activities()->first());
        $this->assertEquals($nonAdminTeacher->user->id, $activity->actor_id);
        $this->assertEquals(Activity::TYPE_CREATE_CLASSROOM, $activity->type);
        $this->assertEquals('created classroom: ' . $classroom->name, $activity->description);
        $this->assertEquals($classroom->id, $activity->data['classroom_id']);
    }

    /**
     * Test that a non-admin teacher cannot create a classroom of which the owner is another teacher in the same school.
     *
     * @return void
     */
    public function test_it_a_non_admin_teacher_cannot_create_a_classroom_for_another_teacher_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher1);

        $this->payload['owner_id'] = $nonAdminTeacher2->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertUnprocessable();

        // Assert that no classroom was created.
        $this->assertDatabaseCount(Classroom::class, 0);
        $this->assertDatabaseCount(ClassroomGroup::class, 0);
        $this->assertDatabaseCount(Activity::class, 0);
    }

    /**
     * Test that a non-admin teacher cannot create a classroom of which the owner is a teacher in another school.
     *
     * @return void
     */
    public function test_a_non_admin_teacher_cannot_create_a_classroom_for_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school1);
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school2);
        $owner = $this->fakeTeacher($school2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $this->payload['owner_id'] = $owner->id;
        $this->payload['year_id'] = $school1->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertUnprocessable();

        // Assert that no classroom was created.
        $this->assertDatabaseCount(Classroom::class, 0);
        $this->assertDatabaseCount(ClassroomGroup::class, 0);
        $this->assertDatabaseCount(Activity::class, 0);
    }

    /**
     * Test that a non-admin teacher can assign secondary teachers to the classroom.
     *
     * @return void
     */
    public function test_a_non_admin_teacher_can_assign_secondary_teachers()
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;
        $this->payload['secondary_teacher_ids'] = [
            $nonAdminTeacher1->id,
            $nonAdminTeacher2->id,
        ];

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated()->assertJsonSuccessful();

        // Assert that the classroom was created correctly.
        $this->assertDatabaseCount(Classroom::class, 1);
        $classroom = Classroom::first();
        $this->assertNotSoftDeleted($classroom);
        $this->assertEquals($school->id, $classroom->school_id);
        $this->assertEquals($this->payload['year_id'], $classroom->year_id);
        $this->assertEquals($nonAdminTeacher->id, $classroom->owner_id);
        $this->assertEquals(Classroom::TYPE_TRADITIONAL_CLASSROOM, $classroom->type);
        $this->assertEquals($this->payload['name'], $classroom->name);
        $this->assertEquals($this->payload['mastery_enabled'], $classroom->mastery_enabled);
        $this->assertEquals($this->payload['self_rating_enabled'], $classroom->self_rating_enabled);

        // Assert that the default classroom group was created correctly.
        $this->assertDatabaseCount(ClassroomGroup::class, 1);
        $this->assertEquals(1, $classroom->classroomGroups()->count());
        $classroomGroup = ClassroomGroup::first();
        $this->assertEquals($classroomGroup, $classroom->defaultClassroomGroup);
        $this->assertEquals($classroom->id, $classroomGroup->classroom_id);
        $this->assertEquals($classroom->name . ' default group', $classroomGroup->name);
        $this->assertEquals($this->payload['pass_grade'], $classroomGroup->pass_grade);
        $this->assertEquals($this->payload['attempts'], $classroomGroup->attempts);
        $this->assertTrue($classroomGroup->is_default);
        $this->assertNotSoftDeleted($classroomGroup);

        // Assert that the activity was created correctly.
        $this->assertDatabaseCount(Activity::class, 1);
        $this->assertEquals(1, $nonAdminTeacher->user->activities()->count());
        $activity = Activity::first();
        $this->assertEquals($activity, $nonAdminTeacher->user->activities()->first());
        $this->assertEquals($nonAdminTeacher->user->id, $activity->actor_id);
        $this->assertEquals(Activity::TYPE_CREATE_CLASSROOM, $activity->type);
        $this->assertEquals('created classroom: ' . $classroom->name, $activity->description);
        $this->assertEquals($classroom->id, $activity->data['classroom_id']);

        // Assert that the secondary teachers were assigned correctly.
        $this->assertDatabaseCount('classroom_secondary_teacher', 2);
        $this->assertEquals(2, $classroom->secondaryTeachers()->count());
    }

    public function test_it_add_classroom_groups()
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);
        $adminTeacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);
        $this->payload['owner_id'] = $adminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;
        $this->payload['groups'] = [
            [
                'name' => $this->faker->name,
                'pass_grade' => $this->faker->numberBetween(0, 100),
                'attempts' => $this->faker->numberBetween(1, 10),
            ],
            [
                'name' => $this->faker->name,
                'pass_grade' => $this->faker->numberBetween(0, 100),
                'attempts' => $this->faker->numberBetween(1, 10),
            ],
        ];

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated()->assertJsonSuccessful();

        // Assert that the classroom was created correctly.
        $this->assertDatabaseCount(Classroom::class, 1);
        $this->assertDatabaseHas(Classroom::class, [
            'school_id' => $school->id,
            'year_id' => $this->payload['year_id'],
            'owner_id' => $this->payload['owner_id'],
            'type' => Classroom::TYPE_TRADITIONAL_CLASSROOM,
            'name' => $this->payload['name'],
            'mastery_enabled' => $this->payload['mastery_enabled'],
            'self_rating_enabled' => $this->payload['self_rating_enabled'],
            'deleted_at' => null,
        ]);
        $classroom = Classroom::first();

        // Assert that the classroom groups was created correctly.
        $this->assertDatabaseCount(ClassroomGroup::class, 3);
        $this->assertDatabaseHas(ClassroomGroup::class, [
            'classroom_id' => $classroom->id,
            'name' => $classroom->name . ' default group',
            'pass_grade' => $this->payload['pass_grade'],
            'attempts' => $this->payload['attempts'],
            'is_default' => true,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas(ClassroomGroup::class, [
            'classroom_id' => $classroom->id,
            'name' => $this->payload['groups'][0]['name'],
            'pass_grade' => $this->payload['groups'][0]['pass_grade'],
            'attempts' => $this->payload['groups'][0]['attempts'],
            'is_default' => false,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas(ClassroomGroup::class, [
            'classroom_id' => $classroom->id,
            'name' => $this->payload['groups'][1]['name'],
            'pass_grade' => $this->payload['groups'][1]['pass_grade'],
            'attempts' => $this->payload['groups'][1]['attempts'],
            'is_default' => false,
            'deleted_at' => null,
        ]);

        // Assert that the activities was created correctly.
        $this->assertDatabaseCount(Activity::class, 3);
        $this->assertDatabaseHas(Activity::class, [
            'actor_id' => $adminTeacher->user->id,
            'type' => Activity::TYPE_CREATE_CLASSROOM,
        ]);
        $this->assertDatabaseHas(Activity::class, [
            'actor_id' => $adminTeacher->user->id,
            'type' => Activity::TYPE_CREATE_CLASSROOM_GROUP,
        ]);
    }
}
