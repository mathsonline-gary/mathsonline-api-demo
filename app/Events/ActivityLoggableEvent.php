<?php

namespace App\Events;

use App\Listeners\LogActivity;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * This is a base class for all events that should be logged to the activity log.
 * It can be used as a parent class for all events that should be listened to by the LogActivity listener.
 *
 * @see LogActivity
 */
abstract class ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User       $actor       The user who performed the action.
     * @param int        $type        The type of activity. It should be one of the activity type constants defined in the Activity model.
     * @param string     $description The description of the activity.
     * @param Carbon     $actedAt     The datetime when the action was performed.
     * @param array|null $data        Additional data to be logged.
     */
    public function __construct(
        public User   $actor,
        public int    $type,
        public string $description,
        public Carbon $actedAt,
        public ?array $data = null,

    )
    {
    }
}
