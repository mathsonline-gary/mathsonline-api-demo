<?php

namespace Tests;

use Database\Seeders\TestSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\TestActivityHelpers;
use Tests\Traits\TestClassroomHelpers;
use Tests\Traits\TestSchoolHelpers;
use Tests\Traits\TestStudentHelpers;
use Tests\Traits\TestTeacherHelpers;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication,
        TestSchoolHelpers,
        TestTeacherHelpers,
        TestStudentHelpers,
        TestClassroomHelpers,
        TestActivityHelpers;

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
