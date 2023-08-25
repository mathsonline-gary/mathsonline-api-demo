<?php

namespace App\Events\Students;

use App\Models\Users\Admin;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentSoftDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Teacher|Admin|null $actor,
        public Student            $student,
    )
    {
    }
}
