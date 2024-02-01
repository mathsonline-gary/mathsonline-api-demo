<?php

namespace App\Http\Resources;

use App\Models\Activity;
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
            'classrooms' => $this->when(
                $this->relationLoaded('classroomGroups') &&
                ($this->classroomGroups->isEmpty() || $this->classroomGroups->first()->relationLoaded('classroom')),
                function () {
                    if (!$this->classroomGroups->isEmpty()) {
                        return $this->classroomGroups->pluck('classroom')->unique('id')->values();
                    }

                    return [];
                }),
            'pass_grade' => $this->whenLoaded('classroomGroups', function () {
                if (!$this->classroomGroups->isEmpty()) {
                    return $this->classroomGroups->max('pass_grade');
                }

                return null;
            }),
            'login_count' => $this->when(
                $this->relationLoaded('user') && $this->asUser()->relationLoaded('activities'),
                function () {
                    if ($this->asUser()->relationLoaded('activities')) {
                        return $this->asUser()->activities
                            ->where('type', Activity::TYPE_LOG_IN)
                            ->count();
                    }

                    return null;
                }),
            'last_login_at' => $this->when(
                $this->relationLoaded('user') && $this->asUser()->relationLoaded('activities'),
                function () {
                    if ($this->asUser()->relationLoaded('activities')) {
                        $activity = $this->asUser()->activities
                            ->where('type', Activity::TYPE_LOG_IN)
                            ->sortByDesc('acted_at')
                            ->first();

                        if (!is_null($activity)) {
                            return $activity->acted_at;
                        }
                    }

                    return null;
                }),
        ];
    }
}
