<?php

namespace App\Http\Requests\Classroom;

use App\Models\Classroom;
use App\Models\Users\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassroomRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:32',
            ],
            'year_id' => [
                'required',
                'int',
            ],
            'owner_id' => [
                'required',
                'int',
            ],
            'pass_grade' => [
                'required',
                'int',
                'min:0',
                'max:100',
            ],
            'attempts' => [
                'required',
                'int',
                'min:1',
            ],
            'secondary_teacher_ids' => ['array'],
            'secondary_teacher_ids.*' => [
                'required',
                'int',
            ],
            'groups' => [
                'array',
                'max:' . Classroom::MAX_CUSTOM_GROUP_COUNT
            ],
            'groups.*.name' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'groups.*.pass_grade' => [
                'required',
                'int',
                'min:0',
                'max:100',
            ],
            'groups.*.attempts' => [
                'required',
                'int',
                'min:1',
            ],
        ];

        // Set rules for teachers.
        /** @var Teacher $teacher */
        if ($teacher = $this->user()->asTeacher()) {
            $rules['year_id'][] = Rule::exists('years', 'id')
                ->where('market_id', $teacher->school->market_id); // Can only create classroom for a year in the same market as the school.

            $rules['owner_id'][] = $teacher->isAdmin()
                ? Rule::exists('teachers', 'id')
                    ->where('school_id', $teacher->school_id)  // Admin teacher can create classroom for any teacher in the school.
                : Rule::exists('teachers', 'id')
                    ->where('id', $teacher->id);   // Non-admin teacher can only create classroom for himself.

            $rules['secondary_teacher_ids.*'][] = Rule::exists('teachers', 'id')
                ->where('school_id', $teacher->school_id); // Can only add secondary teacher from the same school.
        }

        return $rules;
    }
}
