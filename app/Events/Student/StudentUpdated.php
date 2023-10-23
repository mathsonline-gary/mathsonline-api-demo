<?php

namespace App\Events\Student;

use App\Enums\ActivityType;
use App\Events\ActivityLoggableEvent;
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
     * @param User $actor The user who updated the student.
     * @param array $payload
     * @param Student $updatedStudent The updated student instance.
     */
    public function __construct(User $actor, array $payload, Student $updatedStudent,
    )
    {
        parent::__construct(
            actor: $actor,
            activityType: ActivityType::UPDATED_STUDENT,
            actedAt: $updatedStudent->updated_at,
            data: [
                'id' => $updatedStudent->id,
                'payload' => $payload,
            ],
        );
    }
}
