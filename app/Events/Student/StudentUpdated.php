<?php

namespace App\Events\Student;

use App\Enums\ActivityType;
use App\Events\ActivityLoggableEvent;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentUpdated extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Teacher $actor The user who updated the student.
     * @param array $before Student's attributes before updated.
     * @param Student $after The updated student instance.
     */
    public function __construct(Teacher $actor, array $before, Student $after,
    )
    {
        parent::__construct(
            actor: $actor,
            activityType: ActivityType::UPDATED_STUDENT,
            actedAt: $after->updated_at,
            data: [
                'before' => $before,
                'after' => $after->getAttributes(),
            ],
        );
    }
}
