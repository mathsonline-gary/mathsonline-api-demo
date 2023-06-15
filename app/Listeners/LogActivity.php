<?php

namespace App\Listeners;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Events\Teachers\TeacherCreated;
use App\Models\Activity;
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
    public function handle($event): void
    {
        if ($event instanceof LoggedIn) {
            $this->activityService->create($event->user, Activity::ACTION_LOG_IN, $event->loggedInAt);
        }

        if ($event instanceof LoggedOut) {
            $this->activityService->create($event->user, Activity::ACTION_LOG_OUT, $event->loggedOutAt);
        }

        if ($event instanceof TeacherCreated) {
            $this->activityService->create($event->creator, Activity::ACTION_CREATE_TEACHER, $event->createdAt, ['teacher_id' => $event->teacher->id]);
        }
    }
}
