<?php

namespace App\Events\Students;

use App\Enums\ActivityType;
use App\Events\ActivityLoggableEvent;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
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
     * @param Teacher $actor The user who deleted the student.
     * @param Student $student The student who was deleted.
     */
    public function __construct(Teacher $actor, Student $student)
    {
        parent::__construct(
            actor: $actor,
            activityType: ActivityType::DELETED_STUDENT,
            actedAt: $student->deleted_at,
            data: [
                'student_id' => $student->id,
            ],
        );
    }
}
