<?php

namespace App\Http\Resources;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Classroom
 */
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
            'year_id' => $this->year_id,
            'owner_id' => $this->owner_id,
            'mastery_enabled' => $this->mastery_enabled,
            'self_rating_enabled' => $this->self_rating_enabled,
            'pass_grade' => $this->defaultClassroomGroup->pass_grade,
            'attempts' => $this->defaultClassroomGroup->attempts,
            'school' => $this->whenLoaded('school'),
            'owner' => $this->whenLoaded('owner'),
            'secondary_teachers' => $this->whenLoaded('secondaryTeachers'),
            'default_group' => $this->whenLoaded('defaultClassroomGroup'),
            'custom_groups' => $this->whenLoaded('customClassroomGroups'),
        ];
    }
}
