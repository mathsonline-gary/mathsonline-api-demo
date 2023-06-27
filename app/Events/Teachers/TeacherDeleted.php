<?php

namespace App\Events\Teachers;

use App\Models\Users\Admin;
use App\Models\Users\Teacher;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $deletedAt;

    public function __construct(
        public Teacher|Admin|null $actor,
        public Teacher            $teacher,
    )
    {
        $this->deletedAt = Carbon::now();
    }
}
