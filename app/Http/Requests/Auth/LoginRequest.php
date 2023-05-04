<?php

namespace App\Http\Requests\Auth;

use App\Models\Users\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private string $guard;

    private array $credentials = [];

    private string $primaryInputKey;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->guard = Auth::getDefaultDriver();

        return in_array($this->guard, ['tutors', 'teachers', 'students', 'admins', 'developers']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                Rule::requiredIf($this->guard == 'tutors'),
                'string',
                'email',
            ],
            'username' => [
                Rule::requiredIf(in_array($this->guard, ['teachers', 'students', 'admins', 'developers'])),
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
            case 'tutors':
                $this->credentials = $this->only('email', 'password');
                $this->primaryInputKey = 'email';

                break;

            case 'teachers':
            case 'students':
            case 'admins':
            case 'developers':
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
    public function authenticate(): User|Authenticatable|null
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
