<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use App\Events\Classrooms\ClassroomCreated;
use App\Http\Controllers\Api\Teachers\V1\ClassroomController;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
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

        Event::fake();

        $this->payload = [
            'name' => fake()->name,
            'pass_grade' => fake()->numberBetween(0, 100),
            'attempts' => fake()->numberBetween(1, 10),
        ];
    }

    public function test_admin_teachers_can_create_classrooms_for_teachers_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;

        $response = $this->postJson(route('api.teachers.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();

        // Assert that the response returns the correct data of the new classroom.
        $response->assertJsonFragment($this->payload);

        // Assert that event ClassroomCreated was dispatched.
        Event::assertDispatched(ClassroomCreated::class, function (ClassroomCreated $event) use ($adminTeacher) {
            return $event->creator->id === $adminTeacher->id;
        });
    }

    public function test_admin_teachers_cannot_create_classrooms_for_teachers_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;

        $response = $this->postJson(route('api.teachers.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }

    public function test_non_admin_teachers_can_create_classrooms_for_themselves(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $this->payload['owner_id'] = $nonAdminTeacher->id;

        $response = $this->postJson(route('api.teachers.v1.classrooms.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();

        // Assert that the response returns the correct data of the new classroom.
        $response->assertJsonFragment($this->payload);

        // Assert that event ClassroomCreated was dispatched.
        Event::assertDispatched(ClassroomCreated::class, function (ClassroomCreated $event) use ($nonAdminTeacher) {
            return $event->creator->id === $nonAdminTeacher->id;
        });
    }

    public function test_non_admin_teachers_cannot_create_classrooms_for_other_teachers_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher1);

        $this->payload['owner_id'] = $nonAdminTeacher2->id;

        $response = $this->postJson(route('api.teachers.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }

    public function test_non_admin_teachers_cannot_create_classrooms_for_other_teachers_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $this->payload['owner_id'] = $nonAdminTeacher2->id;

        $response = $this->postJson(route('api.teachers.v1.classrooms.store', $this->payload));

        // Assert that the response has a 422 status code.
        $response->assertStatus(422);
    }
}
