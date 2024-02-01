<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateTeacherRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['string', 'min:3', 'max:32', Rule::unique('teachers')->ignore($this->teacher->id)],
            'email' => [
                'nullable',
                'email',
                'min:4',
                'max:128',
                Rule::unique('teachers')->ignore($this->route('teacher')),
            ],
            'first_name' => ['string', 'max:32'],
            'last_name' => ['string', 'max:32'],
            'password' => ['string', Password::defaults(), 'min:4', 'max:32'],
            'title' => ['nullable', 'string', 'max:16'],
            'position' => ['nullable', 'string', 'max:128'],
            'is_admin' => ['boolean'],
        ];
    }
}
