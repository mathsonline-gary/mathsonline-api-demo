<?php

namespace App\Services;

use App\Models\Users\Admin;
use App\Models\Users\Developer;
use App\Models\Users\Member;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Send password reset link.
     *
     * We will send the password reset link to this user. Once we have attempted to send the link, we will examine the response then see the message we need to show to the user. Finally, we'll send out a proper response.
     *
     * @param array $credentials
     *
     * @return string
     */
    public function sendPasswordResetLink(array $credentials): string
    {
        $status = Password::sendResetLink($credentials);

        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }

    /**
     * Reset password.
     *
     * Here we will attempt to reset the user's password. If it is successful we will update the password on an actual user model and persist it to the database. Otherwise, we will parse the error and return the response.
     *
     * @param Request $request
     *
     * @return string
     */
    public function resetPassword(Request $request): string
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->input('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }

    /**
     * Get the authenticated teacher.
     *
     * @return Teacher|null
     */
    public function teacher(): ?Teacher
    {
        return $this->user()?->asTeacher();
    }

    /**
     * Get the authenticated student.
     *
     * @return Student|null
     */
    public function student(): ?Student
    {
        return $this->user()?->asStudent();
    }

    /**
     * Get the authenticated member.
     *
     * @return Member|null
     */
    public function member(): ?Member
    {
        return $this->user()?->asMember();
    }

    /**
     * Get the authenticated admin.
     *
     * @return Admin|null
     */
    public function admin(): ?Admin
    {
        return $this->user()?->asAdmin();
    }

    /**
     * Get the authenticated developer.
     *
     * @return Developer|null
     */
    public function developer(): ?Developer
    {
        return $this->user()?->asDeveloper();
    }

    /**
     * Get current authenticated user.
     *
     * @return User|null
     */
    public function user(): ?User
    {
        return auth()->user();
    }
}
