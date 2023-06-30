<?php

namespace App\Http\Requests\Classrooms;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:32',
            ],
            'owner_id' => [
                'required',
                'int',
                'exists:teachers,id',
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
                'exists:teachers,id',
            ],
            'groups' => [
                'array',
                'max:8'
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
    }
}
