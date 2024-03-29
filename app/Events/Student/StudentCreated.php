<?php

namespace App\Events\Student;

use App\Events\ActivityLoggableEvent;
use App\Models\Activity;
use App\Models\Users\Student;
use App\Models\Users\User;
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
     * @param User    $actor   The user who created the student.
     * @param Student $student The student who was created.
     */
    public function __construct(User $actor, Student $student)
    {
        parent::__construct(
            actor: $actor,
            type: Activity::TYPE_CREATE_STUDENT,
            description: "created student: $student->first_name $student->last_name",
            actedAt: $student->created_at,
            data: [
                'id' => $student->id,
            ],
        );
    }
}
