<?php

namespace Tests\Feature\TeacherApis\Classrooms;

use App\Events\Classrooms\ClassroomDeleted;
use App\Http\Controllers\Api\Teachers\V1\ClassroomController;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @see ClassroomController::destroy()
 */
class DeleteClassroomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function test_admin_teachers_can_delete_classrooms_from_the_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $adminTeacher = $this->createAdminTeacher($school);
        $nonAdminTeacher = $this->createNonAdminTeacher($school);
        $classroom = $this->createClassroom($nonAdminTeacher);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has 204 status code and no content.
        $response->assertNoContent();

        // Assert that ClassroomDeleted event was dispatched.
        Event::assertDispatched(ClassroomDeleted::class, function (ClassroomDeleted $event) use ($adminTeacher, $classroom) {
            return $event->actor->id === $adminTeacher->id &&
                $event->classroom->id === $classroom->id;
        });
    }

    public function test_admin_teachers_are_unauthorised_to_delete_classrooms_in_another_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school1 = $this->createTraditionalSchool();
        $adminTeacher1 = $this->createAdminTeacher($school1);

        $school2 = $this->createTraditionalSchool();
        $adminTeacher2 = $this->createAdminTeacher($school2);
        $classroom = $this->createClassroom($adminTeacher2);

        $this->actingAsTeacher($adminTeacher1);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

    }

    public function test_non_admin_teachers_can_delete_classrooms_owned_by_them(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $nonAdminTeacher = $this->createNonAdminTeacher($school);
        $classroom = $this->createClassroom($nonAdminTeacher);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has 204 status code and no content.
        $response->assertNoContent();

        // Assert that ClassroomDeleted event was dispatched.
        Event::assertDispatched(ClassroomDeleted::class, function (ClassroomDeleted $event) use ($nonAdminTeacher, $classroom) {
            return $event->actor->id === $nonAdminTeacher->id &&
                $event->classroom->id === $classroom->id;
        });
    }

    public function test_non_admin_teachers_are_unauthorised_to_delete_classrooms_that_are_not_owned_by_them(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();

        $nonAdminTeacher1 = $this->createNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->createNonAdminTeacher($school);

        $classroom = $this->createClassroom($nonAdminTeacher2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }
}
