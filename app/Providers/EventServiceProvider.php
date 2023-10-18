<?php

namespace App\Providers;

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
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Google\GoogleExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
            LogActivity::class,
        ],

        LoggedOut::class => [
            LogActivity::class,
        ],
        // --------------------------------------------------------------------------------

        // Teacher events mappings
        // --------------------------------------------------------------------------------
        TeacherCreated::class => [
            LogActivity::class,
        ],

        TeacherDeleted::class => [
            LogActivity::class,
        ],

        TeacherUpdated::class => [
            LogActivity::class,
        ],
        // --------------------------------------------------------------------------------

        // Classroom events mappings
        // --------------------------------------------------------------------------------
        ClassroomCreated::class => [
            LogActivity::class,
        ],

        ClassroomUpdated::class => [
            LogActivity::class,
        ],

        ClassroomDeleted::class => [
            LogActivity::class,
        ],

        ClassroomGroupCreated::class => [
            LogActivity::class,
        ],
        // --------------------------------------------------------------------------------

        // Student events mappings
        // --------------------------------------------------------------------------------
        StudentCreated::class => [
            LogActivity::class,
        ],

        StudentUpdated::class => [
            LogActivity::class,
        ],
        StudentDeleted::class => [
            LogActivity::class,
        ],
        // --------------------------------------------------------------------------------

        // Socialite events mappings
        SocialiteWasCalled::class => [
            GoogleExtendSocialite::class . '@handle',
        ],
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
