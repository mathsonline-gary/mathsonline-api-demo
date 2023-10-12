<?php

namespace App\Http\Requests\Classroom;

use App\Models\Users\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddSecondaryTeacherRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'teacher_id' => [
                'required',
                'int',
                'exists:teachers,id',
            ],
        ];

        // Set rules for teacher users.
        /** @var Teacher $teacher */
        if ($teacher = $this->user()->asTeacher()) {
            $rules['teacher_id'][] = Rule::exists('teachers', 'id')
                ->where('school_id', $teacher->school_id); // Can only add secondary teacher from the same school.
        }

        return $rules;
    }
}
