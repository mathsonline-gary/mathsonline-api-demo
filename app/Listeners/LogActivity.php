<?php

namespace App\Listeners;

use App\Enums\ActivityTypes;
use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Events\Teachers\TeacherCreated;
use App\Events\Teachers\TeacherDeleted;
use App\Events\Teachers\TeacherUpdated;
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
            $this->activityService->create($event->user, ActivityTypes::LOGGED_IN, $event->loggedInAt);
        }

        if ($event instanceof LoggedOut) {
            $this->activityService->create($event->user, ActivityTypes::LOGGED_OUT, $event->loggedOutAt);
        }

        if ($event instanceof TeacherCreated) {
            $this->activityService->create($event->creator, ActivityTypes::CREATED_TEACHER, $event->createdAt, ['teacher_id' => $event->teacher->id]);
        }

        if ($event instanceof TeacherDeleted) {
            $this->activityService->create($event->actor, ActivityTypes::DELETED_TEACHER, $event->deletedAt, ['teacher' => $event->teacher]);
        }

        if ($event instanceof TeacherUpdated) {
            $this->activityService->create($event->actor, ActivityTypes::UPDATED_TEACHER, $event->after->updated_at, [
                'before' => $event->before,
                'after' => $event->after->getAttributes(),
            ]);
        }
    }
}
