<?php

namespace Feature\Classrooms;

use App\Http\Controllers\Api\V1\ClassroomController;
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
            'self-rating_enabled' => fake()->boolean,
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
    public function test_an_admin_teacher_can_create_classroom_for_a_teacher_in_the_same_school(): void
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
    public function test_an_admin_teacher_cannot_create_a_classroom_for_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
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

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();
    }

    public function test_non_admin_teachers_cannot_create_classrooms_for_another_teacher_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher1);

        $this->payload['owner_id'] = $nonAdminTeacher2->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }

    /**
     * Authorization test.
     */
    public function test_a_non_admin_teachers_cannot_create_a_classroom_for_another_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $this->payload['owner_id'] = $nonAdminTeacher2->id;

        $response = $this->postJson(route('api.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }
}
