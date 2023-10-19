<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'username' => ['required', 'string', 'unique:students', 'min:3', 'max:32'],
            'email' => ['nullable', 'email'],
            'first_name' => ['required', 'string', 'max:32'],
            'last_name' => ['required', 'string', 'max:32'],
            'password' => ['required', 'confirmed', Password::defaults(), 'min:4', 'max:32'],
        ];

        // Set rules for teacher users.
        if ($this->user()->isTeacher()) {
            $rules['expired_tasks_excluded'] = ['required', 'boolean'];
            $rules['confetti_enabled'] = ['required', 'boolean'];
        }

        return $rules;
    }
}
