<?php

namespace App\Services;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Login a user by valid credentials.
     *
     * @param LoginRequest $request
     * @return void
     */
    public function login(LoginRequest $request): void
    {
        $request->authenticate();

        $request->session()->regenerate();
    }

    /**
     * Logout current authenticated user.
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request): void
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
    }

    /**
     * Send password reset link.
     *
     * We will send the password reset link to this user. Once we have attempted to send the link, we will examine the response then see the message we need to show to the user. Finally, we'll send out a proper response.
     *
     * @param array $credentials
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
        return auth('teacher')->user();
    }

    /**
     * Get the authenticated student.
     *
     * @return Student|null
     */
    public function student(): ?Student
    {
        return auth('student')->user();
    }
}
