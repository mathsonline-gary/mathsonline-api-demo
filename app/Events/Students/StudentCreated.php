<?php

namespace App\Events\Students;

use App\Enums\ActivityTypes;
use App\Events\ActivityLoggableEvent;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentCreated extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $createdAt;

    /**
     * Create a new event instance.
     *
     * @param Teacher $actor The user who created the student.
     * @param Student $student The student who was created.
     */
    public function __construct(Teacher $actor, Student $student,
    )
    {
        parent::__construct(
            actor: $actor,
            activityType: ActivityTypes::CREATED_STUDENT,
            actedAt: $student->created_at,
            data: [
                'student_id' => $student->id,
            ],
        );
    }
}
