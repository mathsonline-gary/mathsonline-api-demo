<?php

namespace Tests\Unit\Listeners;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Events\Classroom\ClassroomCreated;
use App\Events\Classroom\ClassroomDeleted;
use App\Events\Classroom\ClassroomGroupCreated;
use App\Events\Classroom\ClassroomUpdated;
use App\Events\Student\StudentCreated;
use App\Events\Student\StudentDeleted;
use App\Events\Student\StudentUpdated;
use App\Events\Teacher\TeacherCreated;
use App\Events\Teacher\TeacherDeleted;
use App\Events\Teacher\TeacherUpdated;
use App\Listeners\LogActivity;
use App\Providers\EventServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LogActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     * @see EventServiceProvider::$listen
     * @see LogActivity
     */
    public function test_it_is_attached_to_events()
    {
        Event::fake();

        // Events to test.
        $events = [
            LoggedIn::class,
            LoggedOut::class,

            TeacherCreated::class,
            TeacherDeleted::class,
            TeacherUpdated::class,

            ClassroomCreated::class,
            ClassroomUpdated::class,
            ClassroomDeleted::class,
            ClassroomGroupCreated::class,

            StudentCreated::class,
            StudentUpdated::class,
            StudentDeleted::class,
        ];

        // Assert that it listens to the events.
        foreach ($events as $event) {
            Event::assertListening($event, LogActivity::class);
        }
    }
}
