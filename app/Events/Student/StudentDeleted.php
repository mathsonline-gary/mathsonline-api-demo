<?php

namespace App\Events\Student;

use App\Events\ActivityLoggableEvent;
use App\Models\Activity;
use App\Models\Users\Student;
use App\Models\Users\User;
use Illuminate\Support\Carbon;

class StudentDeleted extends ActivityLoggableEvent
{
    /**
     * The datetime when the student was deleted.
     *
     * @var Carbon
     */
    public Carbon $deletedAt;

    /**
     * Create a new event instance.
     *
     * @param User    $actor   The user who deleted the student.
     * @param Student $student The student who was deleted.
     */
    public function __construct(User $actor, Student $student)
    {
        parent::__construct(
            actor: $actor,
            type: Activity::TYPE_DELETE_STUDENT,
            actedAt: $student->deleted_at,
            data: [
                'student_id' => $student->id,
            ],
        );
    }
}
