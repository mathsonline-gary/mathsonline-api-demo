<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
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
            'type' => $this->type,
            'name' => $this->name,
            'owner_id' => $this->owner_id,
            'pass_grade' => $this->defaultClassroomGroup->pass_grade,
            'attempts' => $this->defaultClassroomGroup->attempts,
            'school' => $this->whenLoaded('school'),
            'owner' => $this->whenLoaded('owner'),
            'secondary_teachers' => $this->whenLoaded('secondaryTeachers'),
            'groups' => $this->whenLoaded('customClassroomGroups'),
        ];
    }
}
