<?php

namespace App\Events\Teachers;

use App\Models\Users\Admin;
use App\Models\Users\Teacher;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $updatedAt;

    /**
     * The teacher's attributes after updated.
     *
     * @var array
     */
    public array $after;

    /**
     * Create a new event instance.
     *
     * @param Teacher|Admin|null $actor The user who updated the teacher.
     * @param array $before Teacher's attributes before updated.
     * @param Teacher $updatedTeacher The updated teacher instance.
     */
    public function __construct(
        public Teacher|Admin|null $actor,
        public array              $before,
        protected Teacher         $updatedTeacher
    )
    {
        $this->updatedAt = $this->updatedTeacher->updated_at;
        $this->after = $this->updatedTeacher->getAttributes();
    }
}
