<?php

namespace App\Http\Requests\Student;

use App\Models\Users\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexStudentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'search_key' => ['string'],
            'page' => ['integer'],
            'per_page' => ['integer'],
            'classroom_id' => ['integer'],
        ];

        // Set rules for teacher users.
        if ($this->user()->isTeacher()) {
            /** @var Teacher $teacher */
            $teacher = $this->user()->asTeacher();

            $availableClassrooms = $teacher->isAdmin() && $this->boolean('all', true)
                ? $teacher->getManagedClassrooms()
                : $teacher->getOwnedAndSecondaryClassrooms();

            // Set rules for 'classroom_id'.
            $rules['classroom_id'][] = Rule::in($availableClassrooms->pluck('id')->toArray());
        }

        return $rules;
    }
}
