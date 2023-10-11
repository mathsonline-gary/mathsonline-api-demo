<?php

namespace App\Http\Requests\Classroom;

use App\Models\Users\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
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
        $rules = [
            'name' => [
                'string',
                'max:32',
            ],
            'year_id' => [
                'int',
            ],
            'owner_id' => [
                'int',
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
            'mastery_enabled' => [
                'boolean',
            ],
            'self_rating_enabled' => [
                'boolean',
            ],
        ];

        // Set rules for teachers.
        /** @var Teacher $teacher */
        if ($teacher = $this->user()->asTeacher()) {
            $rules['year_id'][] = Rule::exists('years', 'id')
                ->where('market_id', $teacher->school->market_id); // Can only create classroom for a year in the same market as the school.


            $rules['owner_id'][] = $teacher->isAdmin()
                ? Rule::exists('teachers', 'id')
                    ->where('school_id', $teacher->school_id)  // Admin teacher can update classroom for teachers in the same school.
                : Rule::exists('teachers', 'id')
                    ->where('id', $teacher->id);   // Non-admin teacher can only update classroom owned by himself.
        }

        return $rules;
    }
}
