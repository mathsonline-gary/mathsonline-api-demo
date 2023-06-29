<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\ActivityHelpers;
use Tests\Traits\ClassroomHelpers;
use Tests\Traits\SchoolHelpers;
use Tests\Traits\StudentHelpers;
use Tests\Traits\TeacherHelpers;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication,
        SchoolHelpers,
        TeacherHelpers,
        StudentHelpers,
        ClassroomHelpers,
        ActivityHelpers;
}
