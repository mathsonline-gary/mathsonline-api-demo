<?php

namespace Tests\Feature\Classrooms;

use Tests\TestCase;

class IndexClassroomTest extends TestCase
{
    public function test_a_guest_cannot_get_classroom_list(): void
    {
        $this->assertGuest();

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert a guest cannot access the endpoint.
        $response->assertUnauthorized();
    }

    public function test_a_teacher_in_an_unsubscribed_school_cannot_get_classroom_list(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $teacher = $this->fakeNonAdminTeacher($school);
            $this->fakeClassroom($teacher);
        }

        $this->actingAsTeacher($teacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert a teacher in an unsubscribed school cannot access the endpoint.
        $response->assertUnsubscribed();
    }

    public function test_an_admin_teacher_can_get_classroom_list(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $adminTeacher = $this->fakeAdminTeacher($school);
            $classrooms = $this->fakeClassroom($adminTeacher, 10);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert that the response is successful.
        $response->assertOk()->assertJsonSuccessful();

        // Assert that the response contains the classrooms.
        $response->assertJsonCount(10, 'data');

        foreach ($classrooms as $classroom) {
            $response->assertJsonFragment([
                'id' => $classroom->id,
            ]);
        }
    }

    public function test_a_non_admin_teachers_can_get_classroom_list(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
            $classrooms = $this->fakeClassroom($nonAdminTeacher, 10);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert that the response is successful.
        $response->assertOk()->assertJsonSuccessful();

        // Assert that the response contains the classrooms.
        $response->assertJsonCount(10, 'data');

        foreach ($classrooms as $classroom) {
            $response->assertJsonFragment([
                'id' => $classroom->id,
            ]);
        }
    }

    public function test_it_only_return_classrooms_in_the_same_school_to_the_teacher(): void
    {
        {
            $school1 = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school1);
            $adminTeacher1 = $this->fakeAdminTeacher($school1);
            $classrooms1 = $this->fakeClassroom($adminTeacher1, 2);

            // Fake classrooms in another school.
            $school2 = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school2);
            $adminTeacher2 = $this->fakeAdminTeacher($school2);
            $classrooms2 = $this->fakeClassroom($adminTeacher2, 2);
        }

        $this->actingAsTeacher($adminTeacher1);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert that the response is successful.
        $response->assertOk()->assertJsonSuccessful();

        // Assert that the response contains the correct number of classrooms.
        $response->assertJsonCount(2, 'data');

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

    public function test_it_only_return_classrooms_owned_by_the_teacher_to_the_non_admin_teacher(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $adminTeacher = $this->fakeAdminTeacher($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
            $classrooms1 = $this->fakeClassroom($nonAdminTeacher, 2);

            // Fake classrooms not owned by the non-admin teacher.
            $classrooms2 = $this->fakeClassroom($adminTeacher, 2);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert that the response is successful.
        $response->assertOk()->assertJsonSuccessful();

        // Assert that the response contains the correct number of classrooms.
        $response->assertJsonCount(2, 'data');

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

    public function test_it_fuzzy_searches_classrooms_by_name(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $adminTeacher = $this->fakeAdminTeacher($school);
            $classroom1 = $this->fakeClassroom($adminTeacher, 1, ['name' => 'Classroom 1']);
            $classroom2 = $this->fakeClassroom($adminTeacher, 1, ['name' => 'Classroom 11']);
            $classroom3 = $this->fakeClassroom($adminTeacher, 1, ['name' => 'Classroom 3']);
        }

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

    public function test_it_responses_to_admin_teacher_with_pagination_by_default(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $adminTeacher = $this->fakeAdminTeacher($school);
            $this->fakeClassroom($adminTeacher, 30);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert that it paginates the response by default with 20 items per page.
        $response->assertJsonCount(20, 'data');
    }

    public function test_it_responses_to_non_admin_teacher_with_pagination_by_default(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
            $this->fakeClassroom($nonAdminTeacher, 30);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert that it paginates the response by default with 20 items per page.
        $response->assertJsonCount(20, 'data');
    }

    public function test_it_can_response_to_admin_teacher_without_pagination(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $adminTeacher = $this->fakeAdminTeacher($school);
            $this->fakeClassroom($adminTeacher, 30);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index', ['pagination' => false]));

        // Assert that the response contains the correct number of classrooms.
        $response->assertJsonCount(30, 'data');
    }

    public function test_it_can_response_to_non_admin_teacher_without_pagination(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
            $this->fakeClassroom($nonAdminTeacher, 30);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index', ['pagination' => false]));

        // Assert that the response contains the correct number of classrooms.
        $response->assertJsonCount(30, 'data');
    }

    public function test_it_can_response_to_admin_teacher_with_custom_pagination(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $adminTeacher = $this->fakeAdminTeacher($school);
            $this->fakeClassroom($adminTeacher, 30);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index', ['per_page' => 10]));

        // Assert that the response contains the correct number of classrooms.
        $response->assertJsonCount(10, 'data');
    }

    public function test_it_can_response_to_non_admin_teacher_with_custom_pagination(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
            $this->fakeClassroom($nonAdminTeacher, 30);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index', ['per_page' => 10]));

        // Assert that the response contains the correct number of classrooms.
        $response->assertJsonCount(10, 'data');
    }

    public function test_it_responses_to_admin_teacher_without_optional_keys_by_default(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $adminTeacher = $this->fakeAdminTeacher($school);
            $classroom = $this->fakeClassroom($adminTeacher);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert that the response does not contain the optional keys by default.
        $response->assertJsonMissing([
            'school',
            'owner',
            'secondary_teachers',
            'groups',
        ]);
    }

    public function test_it_responses_to_non_admin_teacher_without_optional_keys_by_default(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
            $classroom = $this->fakeClassroom($nonAdminTeacher);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index'));

        // Assert that the response does not contain the optional keys by default.
        $response->assertJsonMissing([
            'school',
            'owner',
            'secondary_teachers',
            'groups',
        ]);
    }

    public function test_it_can_response_to_admin_teacher_with_optional_keys(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $adminTeacher = $this->fakeAdminTeacher($school);
            $classroom = $this->fakeClassroom($adminTeacher);
        }

        $this->actingAsTeacher($adminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index', [
            'with_school' => true,
            'with_owner' => true,
            'with_secondary_teachers' => true,
            'with_groups' => true,
        ]));

        // Assert that the response contains the optional keys.
        $response->assertJsonStructure([
            'data' => [
                [
                    'school',
                    'owner',
                    'secondary_teachers',
                    'default_group',
                    'custom_groups',
                ],
            ],
        ]);
    }

    public function test_it_can_response_to_non_admin_teacher_with_optional_keys(): void
    {
        {
            $school = $this->fakeTraditionalSchool();
            $this->fakeSubscription($school);
            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
            $classroom = $this->fakeClassroom($nonAdminTeacher);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.v1.classrooms.index', [
            'with_school' => true,
            'with_owner' => true,
            'with_secondary_teachers' => true,
            'with_groups' => true,
        ]));

        // Assert that the response contains the optional keys.
        $response->assertJsonStructure([
            'data' => [
                [
                    'school',
                    'owner',
                    'secondary_teachers',
                    'default_group',
                    'custom_groups',
                ],
            ],
        ]);
    }
}
