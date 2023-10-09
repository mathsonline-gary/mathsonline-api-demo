<?php

namespace App\Events\Classroom;

use App\Models\Classroom;
use App\Models\Users\Admin;
use App\Models\Users\Teacher;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $deletedAt;

    public function __construct(
        public Teacher|Admin|null $actor,
        public Classroom          $classroom,
    )
    {
        $this->deletedAt = Carbon::now();
    }
}
