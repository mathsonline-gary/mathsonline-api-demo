<?php

namespace App\Events\Classrooms;

use App\Models\Classroom;
use App\Models\Users\Admin;
use App\Models\Users\Teacher;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $createdAt;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Teacher|Admin|null $creator,
        public Classroom          $classroom,
    )
    {
        $this->createdAt = $this->classroom->created_at;
    }
}
