<?php

namespace App\Events\Classroom;

use App\Events\ActivityLoggableEvent;
use App\Models\Activity;
use App\Models\Classroom;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomUpdated extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param User      $actor            The user who update the classroom.
     * @param array     $payload          The request payload.
     * @param Classroom $updatedClassroom The updated classroom.
     */
    public function __construct(User $actor, array $payload, Classroom $updatedClassroom)
    {
        parent::__construct(
            actor: $actor,
            type: Activity::TYPE_UPDATE_CLASSROOM,
            actedAt: $updatedClassroom->updated_at,
            data: [
                'id' => $updatedClassroom->id,
                'payload' => $payload,
            ],
        );
    }
}
