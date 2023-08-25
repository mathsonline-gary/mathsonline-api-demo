<?php

namespace App\Events\Students;

use App\Models\Users\Admin;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Teacher|Admin|null $actor The user who updated the student.
     * @param array $before Student's attributes before updated.
     * @param Student $after The updated student instance.
     */
    public function __construct(
        public Teacher|Admin|null $actor,
        public array              $before,
        public Student            $after,
    )
    {
    }
}
