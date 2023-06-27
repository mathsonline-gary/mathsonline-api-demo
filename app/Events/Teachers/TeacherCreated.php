<?php

namespace App\Events\Teachers;

use App\Models\Users\Admin;
use App\Models\Users\Teacher;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $createdAt;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Teacher|Admin|null $creator,
        public Teacher            $teacher,
    )
    {
        $this->createdAt = $this->teacher->created_at;
    }
}
