<?php

namespace App\Events\Auth;

use App\Models\Users\Admin;
use App\Models\Users\Developer;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\Member;
use App\Models\Users\User;
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
     * @param User|null $user
     */
    public function __construct(
        public User|null $user,
    )
    {
        $this->loggedInAt = Carbon::now();
    }
}
