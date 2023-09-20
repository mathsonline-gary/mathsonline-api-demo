<?php

namespace App\Events;

use App\Enums\ActivityTypes;
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
     * @param User $actor The user who performed the action.
     * @param ActivityTypes $activityType The type of the activity.
     * @param Carbon $actedAt The datetime when the action was performed.
     * @param array|null $data Additional data to be logged.
     */
    public function __construct(
        public User          $actor,
        public ActivityTypes $activityType,
        public Carbon        $actedAt,
        public ?array        $data = null,

    )
    {
    }
}
