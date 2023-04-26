<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private string $role;

    private string $guard;

    private array $credentials = [];

    private string $primaryInputKey;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->guard = Auth::getDefaultDriver();

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                Rule::in([$this->guard])
            ],
            'email' => [
                Rule::requiredIf($this->guard === 'tutor'),
                'string',
                'email',
            ],
            'username' => [
                Rule::requiredIf(in_array($this->guard, ['teacher', 'student', 'admin', 'developer'])),
                'string',
            ],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Handle a passed validation attempt.
     * This method is called after the validation passes, but before the controller method is executed.
     *
     * @return void
     */
    protected function passedValidation(): void
    {
        switch ($this->guard) {
            case 'tutor':
                $this->credentials = $this->only('email', 'password');
                $this->primaryInputKey = 'email';

                break;

            case 'teacher':
            case 'student':
            case 'admin':
            case 'developer':
                $this->credentials = $this->only('username', 'password');
                $this->primaryInputKey = 'username';

                break;
        }
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable|null
    {
        $this->ensureIsNotRateLimited();

        if (
            !Auth::attempt($this->credentials, $this->boolean('remember'))
        ) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $this->primaryInputKey => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return Auth::user();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            $this->primaryInputKey => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input($this->primaryInputKey)) . '|' . $this->ip());
    }
}
