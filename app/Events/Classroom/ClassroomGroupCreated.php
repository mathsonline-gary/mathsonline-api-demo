<?php

namespace App\Events\Classroom;

use App\Events\ActivityLoggableEvent;
use App\Models\Activity;
use App\Models\ClassroomGroup;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomGroupCreated extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(User $creator, ClassroomGroup $classroomGroup)
    {
        parent::__construct(
            actor: $creator,
            type: Activity::TYPE_CREATE_CLASSROOM_GROUP,
            actedAt: $classroomGroup->created_at,
            data: [
                'id' => $classroomGroup->id,
            ],
        );
    }
}
