<?php

namespace App\Events\Students;

use App\Models\Users\Admin;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $createdAt;

    /**
     * Create a new event instance.
     *
     * @param Teacher|Admin|null $actor The user who created the student.
     * @param Student $student The student who was created.
     */
    public function __construct(
        public Teacher|Admin|null $actor,
        public Student            $student,
    )
    {
        $this->createdAt = $this->student->created_at;
    }
}
