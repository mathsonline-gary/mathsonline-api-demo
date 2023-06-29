<?php

namespace App\Events\Teachers;

use App\Models\Users\Admin;
use App\Models\Users\Teacher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Teacher|Admin|null $actor The user who updated the teacher.
     * @param array $before Teacher's attributes before updated.
     * @param Teacher $after The updated teacher instance.
     */
    public function __construct(
        public Teacher|Admin|null $actor,
        public array              $before,
        public Teacher            $after,
    )
    {
    }
}
