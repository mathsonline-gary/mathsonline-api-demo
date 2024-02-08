<?php

namespace App\Events\Teacher;

use App\Events\ActivityLoggableEvent;
use App\Models\Activity;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherCreated extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(User $creator, Teacher $teacher)
    {
        parent::__construct(
            actor: $creator,
            type: Activity::TYPE_CREATE_TEACHER,
            description: "created teacher: $teacher->first_name $teacher->last_name",
            actedAt: $teacher->created_at,
            data: [
                'id' => $teacher->id,
            ],
        );
    }
}
