<?php

namespace Tests\Feature\TeacherApis\Auth;

use App\Http\Controllers\Web\Teachers\AuthController;
use Tests\TestCase;

class AuthenticatedTeacherTest extends TestCase
{
    /**
     * @see AuthController::me()
     */
    public function test_an_admin_teacher_can_view_their_personal_profile(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $response = $this->getJson(route('api.teachers.v1.me'));

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $teacher->id]);
    }

    /**
     * @see AuthController::me()
     */
    public function test_a_non_admin_teacher_can_view_their_personal_profile(): void
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $response = $this->getJson(route('api.teachers.v1.me'));

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $teacher->id]);
    }
}
