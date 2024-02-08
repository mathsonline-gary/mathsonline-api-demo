<?php

namespace App\Events\Teacher;

use App\Events\ActivityLoggableEvent;
use App\Models\Activity;
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
            type: Activity::TYPE_DELETE_TEACHER,
            description: "deleted teacher: $teacher->first_name $teacher->last_name",
            actedAt: $teacher->deleted_at,
            data: [
                'teacher_id' => $teacher->id,
            ],
        );
    }
}
