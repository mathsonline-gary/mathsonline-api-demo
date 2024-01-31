<?php

namespace App\Events\Teacher;

use App\Events\ActivityLoggableEvent;
use App\Models\Activity;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherUpdated extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User    $actor          The user who updated the teacher.
     * @param array   $before         Teacher's attributes before updated.
     * @param Teacher $updatedTeacher The updated teacher instance.
     */
    public function __construct(User $actor, array $before, Teacher $updatedTeacher)
    {
        parent::__construct(
            actor: $actor,
            type: Activity::TYPE_UPDATE_TEACHER,
            actedAt: $updatedTeacher->updated_at,
            data: [
                'before' => $before,
                'after' => $updatedTeacher->getAttributes(),
            ],
        );

    }
}
