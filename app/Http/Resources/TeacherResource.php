<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
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
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'title' => $this->title,
            'position' => $this->position,
            'is_admin' => $this->is_admin,
            'school_id' => $this->school_id,
            'school' => $this->whenLoaded('school'),
            'classrooms_count' => $this->whenCounted('classrooms'),
            'classrooms_as_owner' => $this->whenLoaded('classroomsAsOwner'),
            'classrooms_as_secondary_teacher' => $this->whenLoaded('classroomsAsSecondaryTeacher'),
        ];
    }
}
