<?php

namespace Tests\Feature\TeacherApis\Teachers;

use App\Http\Controllers\Api\Teachers\V1\TeacherController;
use App\Http\Requests\TeacherRequests\StoreTeacherRequest;
use App\Models\Users\Teacher;
use App\Policies\TeacherPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

        $this->payload = [
            'username' => fake()->userName,
            'email' => fake()->safeEmail,
            'password' => 'password',
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'position' => fake()->word,
            'title' => 'Mr',
            'is_admin' => fake()->boolean,
        ];
    }

    public function test_an_admin_teacher_can_add_a_teacher_into_their_school()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $teachersCount = Teacher::count();

        $this->actingAsTeacher($adminTeacher);

        $response = $this->postJson(route('api.teachers.v1.teachers.store', $this->payload));

        // Assert that the response has a 201 “Created” status code.
        $response->assertCreated();

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

        // Assert that the teacher was created in the database.
        $this->assertDatabaseCount('teachers', $teachersCount + 1);

        // Assert that the teacher was created in the database with correct data.
        $teacher = Teacher::latest('id')->first();
        $this->assertEquals($school->id, $teacher->school_id);
        $this->assertEquals($this->payload['username'], $teacher->username);
        $this->assertEquals($this->payload['email'], $teacher->email);
        $this->assertEquals($this->payload['first_name'], $teacher->first_name);
        $this->assertEquals($this->payload['last_name'], $teacher->last_name);
        $this->assertEquals($this->payload['position'], $teacher->position);
        $this->assertEquals($this->payload['title'], $teacher->title);
        $this->assertTrue($teacher->is_admin);
        $this->assertTrue(Hash::check($this->payload['password'], $teacher->password));
    }

    /**
     * @see TeacherPolicy::create()
     */
    public function test_a_non_admin_teacher_is_unauthorized_to_add_a_teacher()
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        $teachersCount = Teacher::count();

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->postJson(route('api.teachers.v1.teachers.store', $this->payload));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }

    /**
     * @see StoreTeacherRequest::rules()
     */
    public function test_username_is_unique()
    {
        $school = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($adminTeacher);

        $this->payload['username'] = $adminTeacher->username;

        $response = $this->postJson(route('api.teachers.v1.teachers.store', $this->payload));

        // Assert that the response has a 422 “Unprocessable Entity” status code.
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }
}
