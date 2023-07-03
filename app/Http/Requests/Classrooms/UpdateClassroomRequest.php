<?php

namespace App\Http\Requests\Classrooms;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassroomRequest extends FormRequest
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
                'string',
                'max:32',
            ],
            'owner_id' => [
                'int',
                Rule::exists('teachers', 'id')
                    ->where(function (Builder $query) {
                        return $query->where('school_id', $this->route('classroom')->school_id);
                    }),
            ],
            'pass_grade' => [
                'int',
                'min:0',
                'max:100',
            ],
            'attempts' => [
                'int',
                'min:1',
            ],
        ];
    }
}
