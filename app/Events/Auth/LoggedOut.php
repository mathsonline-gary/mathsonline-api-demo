<?php

namespace App\Events\Auth;

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoggedOut
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public Carbon $loggedOutAt;

    /**
     * Create a new event instance.
     *
     * @param User|null $user
     */
    public function __construct(
        public User|null $user,
    )
    {
        $this->loggedOutAt = Carbon::now();
    }
}
