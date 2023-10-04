<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['string', Rule::unique('students')->ignore($this->student->id), 'min:3', 'max:32'],
            'email' => ['nullable', 'email'],
            'first_name' => ['string', 'max:32'],
            'last_name' => ['string', 'max:32'],
            'password' => ['string', Password::defaults(), 'min:4', 'max:32'],
        ];
    }
}
