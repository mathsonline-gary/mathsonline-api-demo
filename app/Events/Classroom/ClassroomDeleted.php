<?php

namespace App\Events\Classroom;

use App\Enums\ActivityType;
use App\Events\ActivityLoggableEvent;
use App\Models\Classroom;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomDeleted extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(User $actor, Classroom $classroom)
    {
        parent::__construct(
            actor: $actor,
            activityType: ActivityType::DELETED_CLASSROOM,
            actedAt: $classroom->deleted_at,
            data: [
                'id' => $classroom->id,
            ],
        );
    }
}
