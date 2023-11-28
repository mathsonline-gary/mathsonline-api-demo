<?php

namespace Tests\Feature\Auth;

use App\Enums\UserType;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_teacher_password_link_can_be_requested(): void
    {
        Notification::fake();

        $teacher = $this->fakeTeacher();

        $response = $this->post('/forgot-password', [
            'email' => $teacher->email,
            'type' => UserType::TEACHER->value,
        ]);

        $response->assertOk()
            ->assertJsonFragment(['status' => __(Password::RESET_LINK_SENT)]);

        Notification::assertSentTo($teacher->asUser(), ResetPassword::class);
    }

    public function test_reset_member_password_link_can_be_requested(): void
    {
        Notification::fake();

        $member = $this->fakeMember();

        $response = $this->post('/forgot-password', [
            'email' => $member->email,
            'type' => UserType::MEMBER->value,
        ]);

        $response->assertOk()
            ->assertJsonFragment(['status' => __(Password::RESET_LINK_SENT)]);

        Notification::assertSentTo($member->asUser(), ResetPassword::class);
    }

    public function test_reset_admin_password_link_can_be_requested(): void
    {
        Notification::fake();

        $admin = $this->fakeAdmin();

        $response = $this->post('/forgot-password', [
            'email' => $admin->email,
            'type' => UserType::ADMIN->value,
        ]);

        $response->assertOk()
            ->assertJsonFragment(['status' => __(Password::RESET_LINK_SENT)]);

        Notification::assertSentTo($admin->asUser(), ResetPassword::class);
    }

    public function test_reset_developer_password_link_can_be_requested(): void
    {
        Notification::fake();

        $developer = $this->fakeDeveloper();

        $response = $this->post('/forgot-password', [
            'email' => $developer->email,
            'type' => UserType::DEVELOPER->value,
        ]);

        $response->assertOk()
            ->assertJsonFragment(['status' => __(Password::RESET_LINK_SENT)]);

        Notification::assertSentTo($developer->asUser(), ResetPassword::class);
    }

    public function test_teacher_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $teacher = $this->fakeTeacher();

        $this->post('/forgot-password', [
            'email' => $teacher->email,
            'type' => UserType::TEACHER->value,
        ]);

        Notification::assertSentTo($teacher->asUser(), ResetPassword::class, function (object $notification) use ($teacher) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $teacher->email,
                'type' => UserType::TEACHER->value,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertOk();

            $response->assertSessionHasNoErrors();

            return true;
        });
    }

    public function test_member_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $member = $this->fakeMember();

        $this->post('/forgot-password', [
            'email' => $member->email,
            'type' => UserType::MEMBER->value,
        ]);

        Notification::assertSentTo($member->asUser(), ResetPassword::class, function (object $notification) use ($member) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $member->email,
                'type' => UserType::MEMBER->value,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertOk();

            $response->assertSessionHasNoErrors();

            return true;
        });
    }

    public function test_admin_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $admin = $this->fakeAdmin();

        $this->post('/forgot-password', [
            'email' => $admin->email,
            'type' => UserType::ADMIN->value,
        ]);

        Notification::assertSentTo($admin->asUser(), ResetPassword::class, function (object $notification) use ($admin) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $admin->email,
                'type' => UserType::ADMIN->value,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertOk();

            $response->assertSessionHasNoErrors();

            return true;
        });
    }

    public function test_developer_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $developer = $this->fakeDeveloper();

        $this->post('/forgot-password', [
            'email' => $developer->email,
            'type' => UserType::DEVELOPER->value,
        ]);

        Notification::assertSentTo($developer->asUser(), ResetPassword::class, function (object $notification) use ($developer) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $developer->email,
                'type' => UserType::DEVELOPER->value,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertOk();

            $response->assertSessionHasNoErrors();

            return true;
        });
    }

}
