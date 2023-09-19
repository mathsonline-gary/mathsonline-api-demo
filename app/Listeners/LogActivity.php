<?php

namespace App\Listeners;

use App\Enums\ActivityTypes;
use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Events\Classrooms\ClassroomCreated;
use App\Events\Classrooms\ClassroomDeleted;
use App\Events\Classrooms\ClassroomUpdated;
use App\Events\Students\StudentCreated;
use App\Events\Students\StudentSoftDeleted;
use App\Events\Students\StudentUpdated;
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

        // Handle teacher events
        // ----------------------------------------------------------------------------------------------------
        if ($event instanceof TeacherCreated) {
            $this->activityService->create($event->creator, ActivityTypes::CREATED_TEACHER, $event->createdAt, ['teacher_id' => $event->teacher->id]);
        }

        if ($event instanceof TeacherDeleted) {
            $this->activityService->create($event->actor, ActivityTypes::DELETED_TEACHER, $event->deletedAt, ['teacher_id' => $event->teacher->id]);
        }

        if ($event instanceof TeacherUpdated) {
            $this->activityService->create($event->actor, ActivityTypes::UPDATED_TEACHER, $event->updatedAt, [
                'before' => $event->before,
                'after' => $event->after,
            ]);
        }
        // ----------------------------------------------------------------------------------------------------

        // Handle classroom events
        // ----------------------------------------------------------------------------------------------------
        if ($event instanceof ClassroomCreated) {
            $this->activityService->create($event->creator, ActivityTypes::CREATED_CLASSROOM, $event->createdAt, ['classroom' => $event->classroom]);
        }

        if ($event instanceof ClassroomUpdated) {
            $this->activityService->create($event->actor, ActivityTypes::UPDATED_CLASSROOM, $event->after->updated_at, [
                'before' => $event->before,
                'after' => $event->after->getAttributes(),
            ]);
        }

        if ($event instanceof ClassroomDeleted) {
            $this->activityService->create($event->actor, ActivityTypes::DELETED_CLASSROOM, $event->deletedAt, ['classroom' => $event->classroom]);
        }
        // ----------------------------------------------------------------------------------------------------

        // Handle student events
        // ----------------------------------------------------------------------------------------------------
        if ($event instanceof StudentCreated) {
            $this->activityService->create($event->actor, ActivityTypes::CREATED_STUDENT, $event->createdAt, ['student' => $event->student]);
        }

        if ($event instanceof StudentUpdated) {
            $this->activityService->create($event->actor, ActivityTypes::UPDATED_STUDENT, $event->after->updated_at, [
                'before' => $event->before,
                'after' => $event->after->getAttributes(),
            ]);
        }

        if ($event instanceof StudentSoftDeleted) {
            $this->activityService->create($event->actor, ActivityTypes::SOFT_DELETED_STUDENT, $event->student->deleted_at, ['student' => $event->student]);
        }
        // ----------------------------------------------------------------------------------------------------

    }
}
