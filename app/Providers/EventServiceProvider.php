<?php

namespace App\Providers;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Events\Teachers\TeacherCreated;
use App\Events\Teachers\TeacherDeleted;
use App\Listeners\LogAction;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [

        // Auth events mappings
        // --------------------------------------------------------------------------------
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        LoggedIn::class => [
            LogAction::class,
        ],

        LoggedOut::class => [
            LogAction::class,
        ],
        // --------------------------------------------------------------------------------

        // Teacher events mappings
        // --------------------------------------------------------------------------------
        TeacherCreated::class => [
            LogAction::class,
        ],

        TeacherDeleted::class => [
            LogAction::class,
        ],
        // --------------------------------------------------------------------------------
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
