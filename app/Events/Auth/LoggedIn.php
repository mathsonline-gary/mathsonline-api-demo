<?php

namespace App\Events\Auth;

use App\Models\Users\Admin;
use App\Models\Users\Developer;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\Tutor;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoggedIn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $loggedInAt;

    /**
     * Create a new event instance.
     *
     * @param Teacher|Student|Admin|Developer|Tutor|null $user
     */
    public function __construct(
        public Teacher|Student|Admin|Developer|Tutor|null $user,
    )
    {
        $this->loggedInAt = Carbon::now();
    }
}
