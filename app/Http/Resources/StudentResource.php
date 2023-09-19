<?php

namespace App\Http\Resources;

use App\Models\Users\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Student
 */
class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'school' => $this->whenLoaded('school'),
            'classroom_groups' => $this->whenLoaded('classroomGroups'),
            'classrooms' => $this->whenLoaded('classroomGroups', function () {
                return $this->classroomGroups->pluck('classroom')->unique('id')->values();
            }),
        ];
    }
}