<?php

namespace Tests\Feature\TeacherApis\Teachers;

use App\Events\Teachers\TeacherCreated;
use App\Models\School;
use App\Models\Users\Teacher;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CreateTeacherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function test_teacher_admins_can_add_a_teacher_in_the_same_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $oldTeachersCount = Teacher::count();

        $this->actingAsTeacher($adminTeacher);

        $payload = [
            'school_id' => $adminTeacher->school_id,
            'username' => 'new.teacher',
            'email' => 'new.teacher@test.com',
            'password' => 'password',
            'first_name' => 'New',
            'last_name' => 'Teacher',
            'position' => 'Assistance',
            'title' => 'Mr',
            'is_admin' => true,
        ];

        $response = $this->postJson(route('api.teachers.v1.teachers.store', $payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();

        // Assert that the count of teachers increased by 1.
        $this->assertEquals($oldTeachersCount + 1, Teacher::count());

        // Assert that the response has correct data of the new teacher.
        $response->assertJsonFragment([
            'school_id' => $adminTeacher->school_id,
            'username' => $payload['username'],
            'email' => $payload['email'],
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'position' => $payload['position'],
            'title' => $payload['title'],
            'is_admin' => $payload['is_admin'],
        ]);

        // Assert that the response does not include the teacher's password
        $response->assertJsonMissing(['password']);

        // Assert that the TeacherCreated event was dispatched with the correct parameters
        Event::assertDispatched(TeacherCreated::class, function ($event) use ($adminTeacher, $payload) {
            return $event->creator->id === $adminTeacher->id &&
                $event->teacher->username === $payload['username'];
        });
    }

    public function test_non_admin_teachers_are_unauthorized_to_add_teacher(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $oldTeachersCount = Teacher::count();

        $this->actingAsTeacher($nonAdminTeacher);

        $payload = [
            'username' => 'new.teacher',
            'email' => 'email',
            'password' => 'password',
            'first_name' => 'New',
            'last_name' => 'Teacher',
            'position' => 'Assistance',
            'title' => 'Mr',
            'is_admin' => true,
        ];

        $response = $this->postJson(route('api.teachers.v1.teachers.store', $payload));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

        // Assert that the count of teachers did not change.
        $this->assertEquals($oldTeachersCount, Teacher::count(),);
    }
}
