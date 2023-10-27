<?php

namespace App\Http\Requests\Student;

use App\Models\ClassroomGroup;
use App\Models\Users\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    /**
     * Indicates whether validation should stop after the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

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

            $rules['expired_tasks_excluded'] = ['required', 'boolean'];
            $rules['confetti_enabled'] = ['required', 'boolean'];
            $rules['classroom_group_ids'] = [
                Rule::requiredIf(!$teacher->isAdmin()), // The field is required if the authenticated user is not an admin.
                'array',
            ];

            // Set rules for items in field 'classroom_group_ids'.
            {
                // Get the valid classrooms for classroom groups.
                $classrooms = $teacher->getManagedClassrooms();

                // Get the valid classroom groups for the authenticated teacher.
                $classroomGroups = ClassroomGroup::whereIn('classroom_id', $classrooms->pluck('id')->toArray())->get();

                // Initialize an array to store classroom IDs to check for duplicates.
                $checkedClassroomIds = [];

                $rules['classroom_group_ids.*'] = [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) use ($classroomGroups, &$checkedClassroomIds) {
                        // The classroom group must be from different classrooms.
                        $classroomGroup = $classroomGroups->find($value);

                        // The classroom group must be from a classroom managed by the authenticated teacher.
                        if (!$classroomGroup) {
                            $fail('You have no access to the selected classroom group.');
                        } else {
                            $classroomId = $classroomGroup->classroom_id;


                            if (in_array($classroomId, $checkedClassroomIds)) {
                                $fail('The selected classroom groups must be from different classrooms.');
                            } else {
                                $checkedClassroomIds[] = $classroomId;
                            }
                        }
                    }
                ];
            }
        }

        return $rules;
    }
}
