<?php

namespace App\Events\Classrooms;

use App\Models\Classroom;
use App\Models\Users\Admin;
use App\Models\Users\Teacher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param Teacher|Admin|null $actor The user who update the classroom.
     * @param array $before Classroom attributes before updated.
     * @param Classroom $after Updated classroom attributes.
     */
    public function __construct(
        public Teacher|Admin|null $actor,
        public array              $before,
        public Classroom          $after,
    )
    {
    }
}
