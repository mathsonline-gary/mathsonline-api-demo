<?php

namespace App\Events\Teachers;

use App\Enums\ActivityTypes;
use App\Events\ActivityLoggableEvent;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherDeleted extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(User $actor, Teacher $teacher,
    )
    {
        parent::__construct(
            actor: $actor,
            activityType: ActivityTypes::DELETED_TEACHER,
            actedAt: $teacher->deleted_at,
            data: [
                'teacher_id' => $teacher->id,
            ],
        );
    }
}
