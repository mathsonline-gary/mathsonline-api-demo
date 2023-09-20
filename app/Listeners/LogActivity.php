<?php

namespace App\Listeners;

use App\Events\ActivityLoggableEvent;
use App\Services\ActivityService;

class LogActivity
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected ActivityService $activityService,
    )
    {
    }

    /**
     * Handle the event.
     */
    public function handle(ActivityLoggableEvent $event): void
    {
        $this->activityService->create(
            actor: $event->actor,
            type: $event->activityType,
            actedAt: $event->actedAt,
            data: $event->data,
        );
    }
}
