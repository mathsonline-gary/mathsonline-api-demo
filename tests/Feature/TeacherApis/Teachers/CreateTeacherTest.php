<?php

namespace Tests\Feature\TeacherApis\Teachers;

use App\Events\Teachers\TeacherCreated;
use App\Http\Controllers\Api\Teachers\V1\TeacherController;
use App\Models\Users\Teacher;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @see TeacherController::store()
 */
class CreateTeacherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The payload to use for creating the teacher.
     *
     * @var array
     */
    protected array $payload;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->payload = [
            'username' => fake()->userName,
            'email' => fake()->safeEmail,
            'password' => 'password',
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'position' => fake()->jobTitle,
            'title' => 'Mr',
            'is_admin' => fake()->boolean,
        ];
    }

    public function test_teacher_admins_can_add_a_teacher_in_the_same_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $oldTeachersCount = Teacher::count();

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.teachers.v1.teachers.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();

        // Assert that the count of teachers increased by 1.
        $this->assertEquals($oldTeachersCount + 1, Teacher::count());

        // Assert that the response has correct data of the new teacher.
        $response->assertJsonFragment([
            'school_id' => $school->id,
            'username' => $this->payload['username'],
            'email' => $this->payload['email'],
            'first_name' => $this->payload['first_name'],
            'last_name' => $this->payload['last_name'],
            'position' => $this->payload['position'],
            'title' => $this->payload['title'],
            'is_admin' => $this->payload['is_admin'],
        ]);

        // Assert that the response does not include the teacher's password
        $response->assertJsonMissing(['password']);

        // Assert that the TeacherCreated event was dispatched with the correct parameters
        Event::assertDispatched(TeacherCreated::class, function ($event) use ($adminTeacher) {
            return $event->creator->id === $adminTeacher->id &&
                $event->teacher->username === $this->payload['username'];
        });
    }

    public function test_non_admin_teachers_are_unauthorized_to_add_teacher(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $oldTeachersCount = Teacher::count();

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->postJson(route('api.teachers.v1.teachers.store', $this->payload));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that the count of teachers did not change.
        $this->assertEquals($oldTeachersCount, Teacher::count());
    }
}
