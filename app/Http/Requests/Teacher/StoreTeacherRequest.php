<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreTeacherRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:32', 'unique:teachers'],
            'email' => ['nullable', 'email', 'min:4', 'max:128'],
            'first_name' => ['required', 'string', 'max:32'],
            'last_name' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string', Password::defaults(), 'min:4', 'max:32'],
            'title' => ['nullable', 'string', 'max:16'],
            'position' => ['nullable', 'string', 'max:128'],
            'is_admin' => ['required', 'boolean'],
        ];
    }
}
