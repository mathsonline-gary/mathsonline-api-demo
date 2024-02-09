<?php

namespace Tests;

use Database\Seeders\TestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Helpers\ActivityTestHelpers;
use Tests\Helpers\AdminTestHelpers;
use Tests\Helpers\ClassroomTestHelpers;
use Tests\Helpers\DeveloperTestHelpers;
use Tests\Helpers\MemberTestHelpers;
use Tests\Helpers\SchoolTestHelpers;
use Tests\Helpers\StripeTestHelpers;
use Tests\Helpers\StudentTestHelpers;
use Tests\Helpers\SubscriptionTestHelpers;
use Tests\Helpers\TeacherTestHelpers;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase,
        CreatesApplication,
        SchoolTestHelpers,
        TeacherTestHelpers,
        StudentTestHelpers,
        ClassroomTestHelpers,
        ActivityTestHelpers,
        MemberTestHelpers,
        SubscriptionTestHelpers,
        StripeTestHelpers,
        DeveloperTestHelpers,
        AdminTestHelpers;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected bool $seed = true;

    /**
     * Run a specific seeder before each test.
     *
     * @var string
     */
    protected string $seeder = TestSeeder::class;

}
