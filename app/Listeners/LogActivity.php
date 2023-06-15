<?php

namespace App\Listeners;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Events\Teachers\TeacherCreated;
use App\Models\Activity;
use App\Services\ActivityService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogActivity implements ShouldQueue
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
            $this->activityService->create($event->user, Activity::ACTION_LOGGED_IN);
        }

        if ($event instanceof LoggedOut) {
            $this->activityService->create($event->user, Activity::ACTION_LOGGED_OUT);
        }

        if ($event instanceof TeacherCreated) {
            $this->activityService->create($event->creator, Activity::ACTION_TEACHER_CREATED, ['teacher_id' => $event->teacher->id]);
        }
    }
}
