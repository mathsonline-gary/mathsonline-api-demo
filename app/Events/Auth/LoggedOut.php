<?php

namespace App\Events\Auth;

use App\Events\ActivityLoggableEvent;
use App\Models\Activity;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoggedOut extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User   $user
     * @param Carbon $loggedOutAt
     */
    public function __construct(
        User   $user,
        Carbon $loggedOutAt,
    )
    {
        parent::__construct(
            actor: $user,
            type: Activity::TYPE_LOG_OUT,
            description: "logged out",
            actedAt: $loggedOutAt,
        );
    }
}
