<?php

namespace Feature\TeacherApis\Auth;

use App\Http\Controllers\Web\Teachers\AuthController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @see AuthController::login()
     */
    public function test_an_admin_teacher_can_login()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $response = $this->postJson(route('teachers.login'), [
            'username' => $teacher->username,
            'password' => 'password',
        ]);

        $response->assertNoContent();
    }

    /**
     * @see AuthController::login()
     */
    public function test_a_non_admin_teacher_can_login()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $response = $this->postJson(route('teachers.login'), [
            'username' => $teacher->username,
            'password' => 'password',
        ]);

        $response->assertNoContent();
    }

    /**
     * @see AuthController::login()
     */
    public function test_an_admin_teacher_cannot_login_with_incorrect_password()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $response = $this->postJson(route('teachers.login'), [
            'username' => $teacher->username,
            'password' => 'incorrect-password',
        ]);

        $response->assertUnprocessable();
    }

    /**
     * @see AuthController::login()
     */
    public function test_a_non_admin_teacher_cannot_login_with_incorrect_password()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $response = $this->postJson(route('teachers.login'), [
            'username' => $teacher->username,
            'password' => 'incorrect-password',
        ]);

        $response->assertUnprocessable();
    }

    /**
     * @see AuthController::logout()
     */
    public function test_an_admin_teacher_can_logout()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $response = $this->postJson(route('teachers.logout'));

        $response->assertNoContent();
    }

    /**
     * @see AuthController::logout()
     */
    public function test_a_non_admin_teacher_can_logout()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $response = $this->postJson(route('teachers.logout'));

        $response->assertNoContent();
    }
}
