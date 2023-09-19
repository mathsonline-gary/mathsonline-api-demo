<?php

namespace App\Http\Requests\TeacherRequests;

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
            'username' => ['required', 'string', 'min:3', 'max:32', Rule::unique('teachers')->ignore($this->teacher->id)],
            'email' => ['nullable', 'email', 'min:4', 'max:128'],
            'first_name' => ['required', 'string', 'min:1', 'max:255'],
            'last_name' => ['required', 'string', 'min:1', 'max:255'],
            'password' => ['string', Password::defaults(), 'min:4', 'max:32'],
            'title' => ['nullable', 'string', 'max:8'],
            'position' => ['nullable', 'string', 'max:32'],
            'is_admin' => ['boolean'],
        ];
    }
}
