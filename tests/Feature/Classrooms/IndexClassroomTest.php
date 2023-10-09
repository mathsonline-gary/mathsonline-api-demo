<?php

namespace Feature\Classrooms;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexClassroomTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authorization test.
     */
    public function test_a_guest_cannot_get_classroom_list()
    {
        $this->assertGuest();

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert a guest cannot access the endpoint.
        $response->assertUnauthorized();
    }

    /**
     * Authorization test.
     */
    public function test_an_admin_teacher_can_get_classroom_list()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $this->fakeClassroom($adminTeacher, 10);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assertions
        $response->assertOk();
    }

    /**
     * Authorization test.
     */
    public function test_a_non_admin_teachers_can_get_classroom_list()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeNonAdminTeacher($school);
        $this->fakeClassroom($teacher);

        $this->actingAsTeacher($teacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert a successful response.
        $response->assertOk();
    }

    public function test_it_only_return_classrooms_in_the_same_school_to_the_teacher()
    {
        $school1 = $this->fakeTraditionalSchool();
        $adminTeacher1 = $this->fakeAdminTeacher($school1);
        $classrooms1 = $this->fakeClassroom($adminTeacher1, 2);

        $school2 = $this->fakeTraditionalSchool();
        $adminTeacher2 = $this->fakeAdminTeacher($school2);
        $classrooms2 = $this->fakeClassroom($adminTeacher2, 2);

        $this->actingAsTeacher($adminTeacher1);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert that the response contains the classrooms in the same school as the authenticated teacher.
        foreach ($classrooms1 as $classroom) {
            $response->assertJsonFragment([
                'id' => $classroom->id,
            ]);
        }

        // Assert that the response does not contain the classrooms in the other school.
        foreach ($classrooms2 as $classroom) {
            $response->assertJsonMissing([
                'id' => $classroom->id,
            ]);
        }
    }

    /**
     * Operation test.
     */
    public function test_it_only_return_classrooms_owned_by_the_teacher_to_the_non_admin_teacher()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $classrooms1 = $this->fakeClassroom($nonAdminTeacher, 2);
        $classrooms2 = $this->fakeClassroom($adminTeacher, 2);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert that the response contains the classrooms owned by the authenticated teacher.
        foreach ($classrooms1 as $classroom) {
            $response->assertJsonFragment([
                'id' => $classroom->id,
            ]);
        }

        // Assert that the response does not contain the classrooms owned by the other teacher.
        foreach ($classrooms2 as $classroom) {
            $response->assertJsonMissing([
                'id' => $classroom->id,
            ]);
        }
    }

    /**
     * Operation test.
     */
    public function test_it_fuzzy_searches_classrooms_by_name()
    {
        $school = $this->fakeTraditionalSchool();
        $adminTeacher = $this->fakeAdminTeacher($school);
        $classroom1 = $this->fakeClassroom($adminTeacher, 1, ['name' => 'Classroom 1']);
        $classroom2 = $this->fakeClassroom($adminTeacher, 1, ['name' => 'Classroom 11']);
        $classroom3 = $this->fakeClassroom($adminTeacher, 1, ['name' => 'Classroom 3']);

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index', ['search_key' => 'classroom 1']));

        // Assert that the response contains the classrooms that match the search key.
        $response->assertJsonFragment([
            'id' => $classroom1->id,
        ])
            ->assertJsonFragment([
                'id' => $classroom2->id,
            ])
            ->assertJsonMissing([
                'id' => $classroom3->id,
            ]);
    }
}
