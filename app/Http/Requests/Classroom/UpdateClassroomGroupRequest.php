<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Add groups.
            'adds' => ['array'],
            'adds.*.name' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'adds.*.pass_grade' => [
                'required',
                'int',
                'min:0',
                'max:100',
            ],
            'adds.*.attempts' => [
                'required',
                'int',
                'min:1',
            ],

            // Remove groups.
            'removes' => ['array'],
            'removes.*' => [
                'required',
                'int',
            ],

            // Update groups.
            'updates' => ['array'],
            'updates.*.id' => [
                'required',
                'int',
            ],
            'updates.*.name' => [
                'string',
                'min:1',
                'max:255',
            ],
            'updates.*.pass_grade' => [
                'int',
                'min:0',
                'max:100',
            ],
            'updates.*.attempts' => [
                'int',
                'min:1',
            ],
        ];
    }
}
