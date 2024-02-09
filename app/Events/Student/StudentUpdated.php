<?php

namespace App\Events\Student;

use App\Events\ActivityLoggableEvent;
use App\Models\Activity;
use App\Models\Users\Student;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentUpdated extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User    $actor          The user who updated the student.
     * @param array   $payload
     * @param Student $updatedStudent The updated student instance.
     */
    public function __construct(User $actor, array $payload, Student $updatedStudent,
    )
    {
        parent::__construct(
            actor: $actor,
            type: Activity::TYPE_UPDATE_STUDENT,
            description: "updated student: $updatedStudent->first_name $updatedStudent->last_name",
            actedAt: $updatedStudent->updated_at,
            data: [
                'id' => $updatedStudent->id,
                'payload' => $payload,
            ],
        );
    }
}
