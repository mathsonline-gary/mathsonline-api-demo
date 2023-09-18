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
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher);

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
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher1 = $this->fakeAdminTeacher($school1);

        $school2 = $this->fakeTraditionalSchool();
        $adminTeacher2 = $this->fakeAdminTeacher($school2);
        $classroom = $this->fakeClassroom($adminTeacher2);

        $this->actingAsTeacher($adminTeacher1);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();

    }

    public function test_non_admin_teachers_can_delete_classrooms_owned_by_them(): void
    {
        $school = $this->fakeTraditionalSchool();
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $classroom = $this->fakeClassroom($nonAdminTeacher);

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
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($nonAdminTeacher2);

        $this->actingAsTeacher($nonAdminTeacher1);

        $response = $this->deleteJson(route('api.teachers.v1.classrooms.destroy', $classroom->id));

        // Assert that the response has a 403 “Forbidden” status code.
        $response->assertForbidden();
    }
}
