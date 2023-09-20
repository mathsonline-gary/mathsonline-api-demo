<?php

namespace App\Events\Teachers;

use App\Enums\ActivityTypes;
use App\Events\ActivityLoggableEvent;
use App\Models\Users\Admin;
use App\Models\Users\Teacher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherUpdated extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Teacher|Admin $actor The user who updated the teacher.
     * @param array $before Teacher's attributes before updated.
     * @param Teacher $updatedTeacher The updated teacher instance.
     */
    public function __construct(Teacher|Admin $actor, array $before, Teacher $updatedTeacher)
    {
        parent::__construct(
            actor: $actor,
            activityType: ActivityTypes::UPDATED_TEACHER,
            actedAt: $updatedTeacher->updated_at,
            data: [
                'before' => $before,
                'after' => $updatedTeacher->getAttributes(),
            ],
        );

    }
}
