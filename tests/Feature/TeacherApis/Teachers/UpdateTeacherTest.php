<?php

namespace Tests\Feature\TeacherApis\Teachers;

use App\Events\Teachers\TeacherUpdated;
use App\Http\Controllers\Api\Teachers\V1\TeacherController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * @see /routes/api/api-teachers.php
 * @see TeacherController::update()
 */
class UpdateTeacherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The payload used to update the teacher.
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
            'password' => 'new-password',
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'position' => fake()->word,
            'title' => 'Mr',
        ];
    }

    public function test_an_admin_teacher_can_update_personal_details(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        // Assert that the response is successful with updated teacher profile.
        $response->assertOk()
            ->assertJsonFragment(Arr::except($this->payload, 'password'))
            ->assertJsonMissingPath('password');

        // Assert that the TeacherUpdated event was dispatched.
        Event::assertDispatched(TeacherUpdated::class);

        // Assert that the teacher was updated in the database.
        $teacher->refresh();
        $this->assertEquals($this->payload['username'], $teacher->username);
        $this->assertEquals($this->payload['email'], $teacher->email);
        $this->assertEquals($this->payload['first_name'], $teacher->first_name);
        $this->assertEquals($this->payload['last_name'], $teacher->last_name);
        $this->assertEquals($this->payload['position'], $teacher->position);
        $this->assertEquals($this->payload['title'], $teacher->title);
        $this->assertTrue(Hash::check($this->payload['password'], $teacher->password));
    }

    public function test_it_prevent_admin_teachers_from_change_their_own_admin_permission()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $this->payload['is_admin'] = false;

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the "is_admin" attribute was not updated.
        $response->assertJsonFragment(['is_admin' => true]);

        // Assert that the "is_admin" attribute was not updated.
        $teacher->refresh();
        $this->assertTrue($teacher->is_admin);
    }

    public function test_a_non_admin_teacher_can_update_personal_details(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        // Assert that the response is successful with updated teacher profile.
        $response->assertOk()
            ->assertJsonFragment(Arr::except($this->payload, 'password'))
            ->assertJsonMissingPath('password');

        // Assert that the TeacherUpdated event was dispatched.
        Event::assertDispatched(TeacherUpdated::class);

        // Assert that the teacher was updated in the database.
        $teacher->refresh();
        $this->assertEquals($this->payload['username'], $teacher->username);
        $this->assertEquals($this->payload['email'], $teacher->email);
        $this->assertEquals($this->payload['first_name'], $teacher->first_name);
        $this->assertEquals($this->payload['last_name'], $teacher->last_name);
        $this->assertEquals($this->payload['position'], $teacher->position);
        $this->assertEquals($this->payload['title'], $teacher->title);
        $this->assertTrue(Hash::check($this->payload['password'], $teacher->password));
    }

    public function test_a_non_admin_teacher_cannot_update_the_is_admin_attribute(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $this->payload['is_admin'] = true;

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertOk();

        // Assert that the "is_admin" attribute was not updated.
        $response->assertJsonFragment(['is_admin' => false]);

        // Assert that the "is_admin" attribute was not updated in the database.
        $teacher->refresh();
        $this->assertFalse($teacher->is_admin);
    }

    public function test_an_admin_teacher_can_update_the_details_of_a_teacher_in_the_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['is_admin'] = true;

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        // Assert that the response is successful with updated teacher profile.
        $response->assertOk()
            ->assertJsonFragment(Arr::except($this->payload, 'password'))
            ->assertJsonMissingPath('password');

        // Assert that the TeacherUpdated event was dispatched.
        Event::assertDispatched(TeacherUpdated::class);

        // Assert that the teacher was updated in the database.
        $teacher->refresh();
        $this->assertEquals($this->payload['username'], $teacher->username);
        $this->assertEquals($this->payload['email'], $teacher->email);
        $this->assertEquals($this->payload['first_name'], $teacher->first_name);
        $this->assertEquals($this->payload['last_name'], $teacher->last_name);
        $this->assertEquals($this->payload['position'], $teacher->position);
        $this->assertEquals($this->payload['title'], $teacher->title);
        $this->assertTrue(Hash::check($this->payload['password'], $teacher->password));
        $this->assertTrue($teacher->is_admin);
    }

    public function test_non_admin_teachers_are_unauthorised_to_update_other_teachers_in_their_school(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertForbidden();

        // Assert that the TeacherUpdated event was not dispatched.
        Event::assertNotDispatched(TeacherUpdated::class);

        // Assert that the teacher was unchanged.
        $originalAttributes = $teacher->getAttributes();
        $teacher->refresh();
        foreach ($originalAttributes as $attribute => $value) {
            $this->assertEquals($value, $teacher->$attribute);
        }
    }

    public function test_an_admin_teacher_is_unauthorised_to_update_the_details_of_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertForbidden();

        // Assert that the TeacherUpdated event was not dispatched.
        Event::assertNotDispatched(TeacherUpdated::class);

        // Assert that the teacher was unchanged.
        $originalAttributes = $teacher->getAttributes();
        $teacher->refresh();
        foreach ($originalAttributes as $attribute => $value) {
            $this->assertEquals($value, $teacher->$attribute);
        }
    }

    public function test_a_non_admin_teacher_is_unauthorised_to_update_the_details_of_a_teacher_in_another_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school1);
        $teacher = $this->fakeNonAdminTeacher($school2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->putJson(route('api.teachers.v1.teachers.update', ['teacher' => $teacher]), $this->payload);

        $response->assertForbidden();

        // Assert that the TeacherUpdated event was not dispatched.
        Event::assertNotDispatched(TeacherUpdated::class);

        // Assert that the teacher was unchanged.
        $originalAttributes = $teacher->getAttributes();
        $teacher->refresh();
        foreach ($originalAttributes as $attribute => $value) {
            $this->assertEquals($value, $teacher->$attribute);
        }
    }
}
