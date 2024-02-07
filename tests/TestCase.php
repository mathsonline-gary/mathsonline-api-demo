<?php

namespace Tests;

use Database\Seeders\TestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\ActivityTestHelpers;
use Tests\Traits\AdminTestHelpers;
use Tests\Traits\ClassroomTestHelpers;
use Tests\Traits\DeveloperTestHelpers;
use Tests\Traits\MemberTestHelpers;
use Tests\Traits\SchoolTestHelpers;
use Tests\Traits\StripeTestHelpers;
use Tests\Traits\StudentTestHelpers;
use Tests\Traits\SubscriptionTestHelpers;
use Tests\Traits\TeacherTestHelpers;

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
