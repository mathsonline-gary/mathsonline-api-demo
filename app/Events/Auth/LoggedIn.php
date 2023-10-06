<?php

namespace App\Events\Auth;

use App\Enums\ActivityType;
use App\Events\ActivityLoggableEvent;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoggedIn extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $actor
     * @param Carbon $loggedInAt
     */
    public function __construct(
        User   $actor,
        Carbon $loggedInAt,
    )
    {
        parent::__construct(
            actor: $actor,
            activityType: ActivityType::LOGGED_IN,
            actedAt: $loggedInAt,
        );
    }
}
