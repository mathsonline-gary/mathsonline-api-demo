<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use App\Http\Controllers\Api\Teachers\V1\ClassroomController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see ClassroomController::show()
 */
class ShowClassroomTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_teachers_can_get_details_of_classrooms_in_his_school(): void
    {

    }

    public function test_admin_teachers_are_unauthorised_to_get_details_of_classrooms_in_other_schools(): void
    {

    }

    public function test_non_admin_teachers_can_get_details_of_classrooms_that_he_owns()
    {

    }

    public function test_non_admin_teachers_are_unauthorised_to_get_details_of_classrooms_that_he_owns()
    {

    }
}
