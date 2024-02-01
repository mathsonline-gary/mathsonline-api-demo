<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Operation test.
     *
     * @see AuthenticatedSessionController::store()
     */
    public function test_a_teacher_can_login_with_correct_credentials(): void
    {
        $teacher = $this->fakeTeacher();

        $this->assertGuest();

        $response = $this->postJson(route('login'), [
            'login' => $teacher->asUser()->login,
            'password' => 'password',
            'type' => 2,
        ]);

        $this->assertAuthenticatedAs($teacher->asUser());

        $response->assertNoContent();
    }

    public function test_it_logs_logged_in_activity()
    {
        $teacher = $this->fakeTeacher();

        $this->assertDatabaseCount('activities', 0);

        $this->assertGuest();

        $this->postJson(route('login'), [
            'login' => $teacher->asUser()->login,
            'password' => 'password',
            'type' => 2,
        ]);

        $this->assertDatabaseCount('activities', 1);

        // Assert that the activity is logged with the correct data.
        $activity = Activity::first();
        $this->assertEquals($teacher->asUser()->id, $activity->actor_id);
        $this->assertEquals(Activity::TYPE_LOG_IN, $activity->type);
        $this->assertNull($activity->data);
        $this->assertEqualsWithDelta(Carbon::now(), $activity->acted_at, 5);
    }

    /**
     * Operation test.
     *
     * @see AuthenticatedSessionController::store()
     */
    public function test_a_teacher_cannot_login_with_invalid_password(): void
    {
        $teacher = $this->fakeTeacher();

        $this->assertGuest();

        $response = $this->postJson(route('login'), [
            'login' => $teacher->asUser()->login,
            'password' => 'invalid-password',
            'type' => 2,
        ]);

        $response->assertUnprocessable();

        $this->assertGuest();
    }

    /**
     * Operation test.
     *
     * @see AuthenticatedSessionController::store()
     * @see LoginRequest::authenticate()
     */
    public function test_a_teacher_cannot_login_with_invalid_type(): void
    {
        $teacher = $this->fakeTeacher();

        $this->assertGuest();

        $response = $this->postJson(route('login'), [
            'login' => $teacher->asUser()->login,
            'password' => 'password',
            'type' => 1,
        ]);

        $response->assertInvalid(['login' => __('auth.failed')]);

        $this->assertGuest();
    }

    /**
     * Authentication test.
     *
     * @see RedirectIfAuthenticated::handle()
     */
    public function test_a_logged_in_teacher_cannot_re_login()
    {
        $teacher = $this->fakeTeacher();

        $this->actingAsTeacher($teacher);

        $this->assertAuthenticatedAs($teacher->asUser());

        $response = $this->postJson(route('login'), [
            'login' => $teacher->asUser()->login,
            'password' => 'password',
            'type' => 2,
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'You are already authenticated.',
            ]);

        $this->assertAuthenticatedAs($teacher->asUser());
    }

    /**
     * Authentication test.
     *
     * @see AuthenticatedSessionController::store()
     */
    public function test_a_soft_deleted_teacher_cannot_login(): void
    {
        $teacher = $this->fakeTeacher();
        $teacher->asUser()->delete();
        $teacher->delete();

        $this->assertGuest();

        $response = $this->postJson(route('login'), [
            'login' => $teacher->asUser()->login,
            'password' => 'invalid-password',
            'type' => 2,
        ]);

        $response->assertInvalid(['login' => __('auth.failed')]);

        $this->assertGuest();
    }

    /**
     * Validation test.
     *
     * @see LoginRequest::rules()
     */
    public function test_login_field_is_required()
    {
        $this->fakeTeacher();

        $this->assertGuest();

        $response = $this->postJson(route('login'), [
            'password' => 'password',
            'type' => 2,
        ]);

        $response->assertInvalid(['login' => __('validation.required', ['attribute' => 'login'])]);

        $this->assertGuest();
    }

    /**
     * Validation test.
     *
     * @see LoginRequest::rules()
     */
    public function test_password_field_is_required()
    {
        $teacher = $this->fakeTeacher();

        $this->assertGuest();

        $response = $this->postJson(route('login'), [
            'login' => $teacher->asUser()->login,
            'type' => 2,
        ]);

        $response->assertInvalid(['password' => __('validation.required', ['attribute' => 'password'])]);

        $this->assertGuest();
    }

    /**
     * Validation test.
     *
     * @see LoginRequest::rules()
     */
    public function test_type_field_is_required()
    {
        $teacher = $this->fakeTeacher();

        $this->assertGuest();

        $response = $this->postJson(route('login'), [
            'login' => $teacher->asUser()->login,
            'password' => 'password',
        ]);

        $response->assertInvalid(['type' => __('validation.required', ['attribute' => 'type'])]);

        $this->assertGuest();
    }
}
