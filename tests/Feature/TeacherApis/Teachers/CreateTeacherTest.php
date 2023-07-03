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

        $school = School::factory()
            ->traditionalSchool()
            ->create();

        $teacherAdmin = Teacher::factory()
            ->ofSchool($school)
            ->admin()
            ->create();

        $oldTeachersCount = Teacher::count();

        $this->actingAs($teacherAdmin, 'teacher');

        $payload = [
            'school_id' => $teacherAdmin->school_id,
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
        $this->assertEquals(1, Teacher::count() - $oldTeachersCount);

        // Assert that the teacher was stored in the database.
        $this->assertDatabaseHas('teachers', [
            'school_id' => $teacherAdmin->school_id,
            'username' => $payload['username'],
        ]);

        // Assert that the response has correct data of the new teacher.
        $response->assertJsonFragment([
            'school_id' => $teacherAdmin->school_id,
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
        Event::assertDispatched(TeacherCreated::class, function ($event) use ($teacherAdmin, $payload) {
            return $event->creator->id === $teacherAdmin->id &&
                $event->teacher->username === $payload['username'];
        });
    }

    public function test_non_admin_teachers_are_unauthorized_to_add_teacher(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = School::factory()
            ->traditionalSchool()
            ->create();

        $nonAdminTeacher = Teacher::factory()
            ->ofSchool($school)
            ->create();

        $oldTeachersCount = Teacher::count();

        $this->actingAs($nonAdminTeacher, 'teacher');

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
        $this->assertEquals(Teacher::count(), $oldTeachersCount);
    }
}
