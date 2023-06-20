<?php

namespace App\Listeners;

use App\Enums\ActionTypes;
use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Events\Teachers\TeacherCreated;
use App\Models\Action;
use App\Services\ActionService;

class LogAction
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected ActionService $actionService,
    )
    {
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        if ($event instanceof LoggedIn) {
            $this->actionService->create($event->user, ActionTypes::LOG_IN, $event->loggedInAt);
        }

        if ($event instanceof LoggedOut) {
            $this->actionService->create($event->user, ActionTypes::LOG_OUT, $event->loggedOutAt);
        }

        if ($event instanceof TeacherCreated) {
            $this->actionService->create($event->creator, ActionTypes::CREATE_TEACHER, $event->createdAt, ['teacher_id' => $event->teacher->id]);
        }
    }
}
