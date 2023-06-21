<?php

namespace Tests\Unit\Listeners;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Events\Teachers\TeacherCreated;
use App\Listeners\LogAction;
use App\Providers\EventServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LogActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     * @see EventServiceProvider::$listen
     * @see LogAction
     */
    public function test_it_is_attached_to_events()
    {
        Event::fake();

        // Assert that it listens to correct events
        Event::assertListening(LoggedIn::class, LogAction::class);
        Event::assertListening(LoggedOut::class, LogAction::class);
        Event::assertListening(TeacherCreated::class, LogAction::class);
    }
}
