<?php

namespace Tests\Feature\Classrooms;

use App\Http\Controllers\Api\V1\ClassroomController;
use App\Models\Activity;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @see ClassroomController::store()
 */
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

    public function test_a_guest_cannot_create_a_classroom()
    {
        $this->assertGuest();

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 401 status code.
        $response->assertUnauthorized();

        // Assert that no classroom was created.
        $this->assertDatabaseCount('classrooms', 0);
        $this->assertDatabaseCount('classroom_groups', 0);
        $this->assertDatabaseCount('activities', 0);
    }

    public function test_a_teacher_in_the_unsubscribed_school_cannot_create_a_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->actingAsTeacher($this->fakeTeacher($school));

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has unsubscription error.
        $response->assertUnsubscribed();

        // Assert that no classroom was created.
        $this->assertDatabaseCount('classrooms', 0);
        $this->assertDatabaseCount('classroom_groups', 0);
        $this->assertDatabaseCount('activities', 0);
    }

    public function test_an_admin_teacher_can_create_a_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school);
        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated()->assertJsonSuccessful();

        // Assert that the classroom was created correctly.
        $this->assertDatabaseCount('classrooms', 1);
        $this->assertDatabaseHas('classrooms', [
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

        // Assert that the default classroom group was created correctly.
        $this->assertDatabaseCount('classroom_groups', 1);
        $this->assertDatabaseHas('classroom_groups', [
            'classroom_id' => $classroom->id,
            'name' => $classroom->name . ' default group',
            'pass_grade' => $this->payload['pass_grade'],
            'attempts' => $this->payload['attempts'],
            'is_default' => true,
            'deleted_at' => null,
        ]);

        // Assert that the activity was created correctly.
        $this->assertDatabaseCount('activities', 1);
        $this->assertDatabaseHas('activities', [
            'actor_id' => $adminTeacher->user->id,
            'type' => Activity::TYPE_CREATE_CLASSROOM,
        ]);
    }

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

        // Assert that no classroom was created.
        $this->assertDatabaseCount('classrooms', 0);
        $this->assertDatabaseCount('classroom_groups', 0);
        $this->assertDatabaseCount('activities', 0);
    }

    public function test_it_does_not_allow_an_admin_teacher_to_create_a_classroom_for_a_teacher_from_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school1);
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school2);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school1->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertUnprocessable();

        // Assert that no classroom was created.
        $this->assertDatabaseCount('classrooms', 0);
        $this->assertDatabaseCount('classroom_groups', 0);
        $this->assertDatabaseCount('activities', 0);
    }

    public function test_it_does_not_allow_a_non_admin_teacher_to_create_a_classroom_for_another_teacher_from_the_same_school(): void
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
        $this->assertDatabaseCount('classrooms', 0);
        $this->assertDatabaseCount('classroom_groups', 0);
    }

    public function test_it_does_not_allow_a_non_admin_teacher_to_create_a_classroom_for_another_teacher_from_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school1);
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $this->fakeSubscription($school2);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $this->payload['owner_id'] = $nonAdminTeacher2->id;
        $this->payload['year_id'] = $school1->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertUnprocessable();

        // Assert that no classroom was created.
        $this->assertDatabaseCount('classrooms', 0);
        $this->assertDatabaseCount('classroom_groups', 0);
        $this->assertDatabaseCount('activities', 0);
    }

    public function test_it_assigns_secondary_teachers()
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
        $this->assertDatabaseCount('classrooms', 1);
        $this->assertDatabaseHas('classrooms', [
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

        // Assert that the default classroom group was created correctly.
        $this->assertDatabaseCount('classroom_groups', 1);
        $this->assertDatabaseHas('classroom_groups', [
            'classroom_id' => $classroom->id,
            'name' => $classroom->name . ' default group',
            'pass_grade' => $this->payload['pass_grade'],
            'attempts' => $this->payload['attempts'],
            'is_default' => true,
            'deleted_at' => null,
        ]);

        // Assert that the secondary teachers were assigned correctly.
        $this->assertDatabaseCount('classroom_secondary_teacher', 2);
        $this->assertDatabaseHas('classroom_secondary_teacher', [
            'classroom_id' => $classroom->id,
            'teacher_id' => $nonAdminTeacher1->id,
        ]);
        $this->assertDatabaseHas('classroom_secondary_teacher', [
            'classroom_id' => $classroom->id,
            'teacher_id' => $nonAdminTeacher2->id,
        ]);

        // Assert that the activity was created correctly.
        $this->assertDatabaseCount('activities', 1);
        $this->assertDatabaseHas('activities', [
            'actor_id' => $adminTeacher->user->id,
            'type' => Activity::TYPE_CREATE_CLASSROOM,
        ]);
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
        $this->assertDatabaseCount('classrooms', 1);
        $this->assertDatabaseHas('classrooms', [
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
        $this->assertDatabaseCount('classroom_groups', 3);
        $this->assertDatabaseHas('classroom_groups', [
            'classroom_id' => $classroom->id,
            'name' => $classroom->name . ' default group',
            'pass_grade' => $this->payload['pass_grade'],
            'attempts' => $this->payload['attempts'],
            'is_default' => true,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('classroom_groups', [
            'classroom_id' => $classroom->id,
            'name' => $this->payload['groups'][0]['name'],
            'pass_grade' => $this->payload['groups'][0]['pass_grade'],
            'attempts' => $this->payload['groups'][0]['attempts'],
            'is_default' => false,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('classroom_groups', [
            'classroom_id' => $classroom->id,
            'name' => $this->payload['groups'][1]['name'],
            'pass_grade' => $this->payload['groups'][1]['pass_grade'],
            'attempts' => $this->payload['groups'][1]['attempts'],
            'is_default' => false,
            'deleted_at' => null,
        ]);

        // Assert that the activities was created correctly.
        $this->assertDatabaseCount('activities', 3);
        $this->assertDatabaseHas('activities', [
            'actor_id' => $adminTeacher->user->id,
            'type' => Activity::TYPE_CREATE_CLASSROOM,
        ]);
        $this->assertDatabaseHas('activities', [
            'actor_id' => $adminTeacher->user->id,
            'type' => Activity::TYPE_CREATE_CLASSROOM_GROUP,
        ]);
    }
}
