<?php

namespace Tests\Unit\Listeners;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
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

        // Assert that it listens to correct events
        Event::assertListening(LoggedIn::class, LogActivity::class);
        Event::assertListening(LoggedOut::class, LogActivity::class);

        Event::assertListening(TeacherCreated::class, LogActivity::class);
        Event::assertListening(TeacherDeleted::class, LogActivity::class);
        Event::assertListening(TeacherUpdated::class, LogActivity::class);

        Event::assertListening(StudentCreated::class, LogActivity::class);
        Event::assertListening(StudentUpdated::class, LogActivity::class);
        Event::assertListening(StudentDeleted::class, LogActivity::class);
    }
}
