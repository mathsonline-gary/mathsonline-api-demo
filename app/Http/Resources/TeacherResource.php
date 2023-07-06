<?php

namespace App\Http\Resources;

use App\Models\Users\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Teacher
 */
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
            'owned_classrooms' => $this->whenLoaded('ownedClassrooms'),
            'secondary_classrooms' => $this->whenLoaded('secondaryClassrooms'),
        ];
    }
}
