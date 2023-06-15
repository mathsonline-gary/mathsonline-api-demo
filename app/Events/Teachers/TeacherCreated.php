<?php

namespace App\Events\Teachers;

use App\Models\Users\Admin;
use App\Models\Users\Teacher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Teacher|Admin|null $creator,
        public Teacher            $teacher,
    )
    {
    }
}
