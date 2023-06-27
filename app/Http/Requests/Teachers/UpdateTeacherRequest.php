<?php

namespace App\Http\Requests\Teachers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateTeacherRequest extends FormRequest
{
    public function rules(): array
    {
        $teacherId = $this->route('teacher')->id;

        return [
            'username' => ['string', Rule::unique('teachers')->ignore($teacherId)],
            'email' => ['nullable', 'email'],
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'password' => ['string', Password::defaults()],
            'title' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['boolean'],
        ];
    }
}
