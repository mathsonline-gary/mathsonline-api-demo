<?php

namespace Tests\Feature\Classrooms;

use App\Enums\ActivityType;
use App\Http\Controllers\Api\V1\ClassroomController;
use App\Models\Activity;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @see ClassroomController::store()
 */
class CreateClassroomTest extends TestCase
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
            'mastery_enabled' => fake()->boolean,
            'self_rating_enabled' => fake()->boolean,
        ];
    }

    /**
     * Authentication test.
     */
    public function test_a_guest_cannot_create_a_classroom()
    {
        $this->assertGuest();

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 401 status code.
        $response->assertUnauthorized();
    }

    /**
     * Authorization test.
     */
    public function test_an_admin_teacher_can_create_a_classroom(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();
    }

    /**
     * Authorization test.
     */
    public function test_a_non_admin_teachers_can_create_a_classroom_for_himself(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();
    }

    public function test_it_creates_the_classroom_correctly()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $this->assertDatabaseCount('classrooms', 0);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the classroom was created correctly.
        $this->assertDatabaseCount('classrooms', 1);
        $classroom = Classroom::first();
        $this->assertEquals($school->id, $classroom->school_id);
        $this->assertEquals($this->payload['name'], $classroom->name);
        $this->assertEquals($nonAdminTeacher->id, $classroom->owner_id);
        $this->assertEquals($this->payload['year_id'], $classroom->year_id);
        $this->assertEquals($this->payload['mastery_enabled'], $classroom->mastery_enabled);
        $this->assertEquals($this->payload['self_rating_enabled'], $classroom->self_rating_enabled);
    }

    public function test_it_assigns_secondary_teachers()
    {
        $school = $this->fakeTraditionalSchool();
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

        $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the secondary teachers were assigned correctly.
        $classroom = Classroom::first();
        $this->assertTrue($classroom->secondaryTeachers->contains($nonAdminTeacher1));
        $this->assertTrue($classroom->secondaryTeachers->contains($nonAdminTeacher2));
    }

    public function test_it_add_classroom_groups()
    {
        $school = $this->fakeTraditionalSchool();
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

        $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the classroom groups were added correctly.
        $this->assertDatabaseCount('classroom_groups', 3); // 1 default + 2 custom (from the payload)
        $classroom = Classroom::first();
        $this->assertEquals(1, $classroom->defaultClassroomGroup()->count());
        $this->assertEquals(2, $classroom->customClassroomGroups()->count());

        // Assert that the created classroom group activities were added correctly.
        $activities = Activity::where('type', ActivityType::CREATED_CLASSROOM_GROUP)->get();
        $this->assertEquals(2, $activities->count());
    }

    /**
     * Validation test.
     */
    public function test_it_does_not_allow_an_admin_teacher_to_create_a_classroom_for_a_teacher_from_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;
        $this->payload['year_id'] = $school1->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }

    /**
     * Validation test.
     */
    public function test_it_does_not_allow_a_non_admin_teacher_to_create_a_classroom_for_another_teacher_from_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher1);

        $this->payload['owner_id'] = $nonAdminTeacher2->id;
        $this->payload['year_id'] = $school->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }

    /**
     * Validation test.
     */
    public function test_it_does_not_allow_a_non_admin_teacher_to_create_a_classroom_for_another_teacher_from_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $this->payload['owner_id'] = $nonAdminTeacher2->id;
        $this->payload['year_id'] = $school1->market->years->random()->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }
}
