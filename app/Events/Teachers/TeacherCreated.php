<?php

namespace App\Events\Teachers;

use App\Enums\ActivityType;
use App\Events\ActivityLoggableEvent;
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
            activityType: ActivityType::CREATED_TEACHER,
            actedAt: $teacher->created_at,
            data: [
                'teacher_id' => $teacher->id,
            ],
        );
    }
}
