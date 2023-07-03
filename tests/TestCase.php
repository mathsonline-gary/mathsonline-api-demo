<?php

namespace Tests;

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
}
