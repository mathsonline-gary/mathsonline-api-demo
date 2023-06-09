<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\ClassroomHelpers;
use Tests\Traits\SchoolHelpers;
use Tests\Traits\TeacherHelpers;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication,
        SchoolHelpers,
        TeacherHelpers,
        ClassroomHelpers;
}
