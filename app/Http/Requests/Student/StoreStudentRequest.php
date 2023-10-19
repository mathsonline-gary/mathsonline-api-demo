<?php

namespace App\Http\Requests\Student;

use App\Models\Users\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            /** @var Teacher $teacher */
            $teacher = $this->user()->asTeacher();
            $classrooms = $teacher->getManagedClassrooms();

            $rules['expired_tasks_excluded'] = ['required', 'boolean'];
            $rules['confetti_enabled'] = ['required', 'boolean'];
            $rules['classroom_group_ids'] = ['array'];
            $rules['classroom_group_ids.*'] = [
                'required',
                'integer',
                Rule::exists('classroom_groups', 'id')
                    ->whereIn('classroom_id', $classrooms->pluck('id')->toArray()), // The classroom group must be from a classroom managed by the authenticated teacher.
            ];

            // Set rules for non-admin users.
            if (!$teacher->isAdmin()) {
                $rules['classroom_group_ids'][] = 'required';
            }
        }

        return $rules;
    }
}
