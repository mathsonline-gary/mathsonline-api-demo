<?php

namespace Tests\Feature\Teachers;

use App\Http\Controllers\Api\V1\TeacherController;
use Tests\TestCase;

/**
 * @see /routes/api/api-teachers.php
 * @see TeacherController::index()
 */
class IndexTeacherTest extends TestCase
{
    protected string $routeName = 'api.v1.teachers.index';

    public function test_a_guest_cannot_get_the_list_of_teachers(): void
    {
        $school = $this->fakeTraditionalSchool();
        $this->fakeTeacher($school, 10);

        $this->assertGuest();

        $response = $this->getJson(route($this->routeName));

        // Assert that the request is unauthorized.
        $response->assertUnauthorized();
    }

    public function test_a_teacher_in_an_unsubscribed_school_cannot_get_the_list_of_teachers(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $teacher = $this->fakeTeacher($school);
        }

        $this->actingAsTeacher($teacher);

        $response = $this->getJson(route($this->routeName));

        $response->assertUnsubscribed();
    }

    public function test_an_admin_teacher_can_only_get_the_list_of_teachers_in_same_school(): void
    {
        {
            $school1 = $this->fakeTraditionalSchool();
            $school2 = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school1);
            $this->fakeSubscription($school2);

            $teacherAdmin = $this->fakeAdminTeacher($school1);
            $this->fakeTeacher($school1, 10);
            $this->fakeTeacher($school2, 10);
        }

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->getJson(route($this->routeName));

        // Assert that the request is successful.
        $response->assertOk()->assertJsonSuccessful();

        // Assert that the response contains the correct number of teachers.
        $response->assertJsonCount(11, 'data');

        // Assert all teachers in school 2 are not included.
        $response->assertJsonMissing([
            'school_id' => $school2->id,
        ]);
    }

    public function test_an_admin_teacher_cannot_view_soft_deleted_teachers_in_the_list(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $teacherAdmin = $this->fakeAdminTeacher($school);

            $deletedTeachers = $this->fakeTeacher($school, 10, ['deleted_at' => now()]);
        }

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->getJson(route($this->routeName));

        // Assert that the request is successful.
        $response->assertOk()->assertJsonSuccessful();

        // Assert that the response contains the correct number of teachers.
        $response->assertJsonCount(1, 'data');

        // Assert that the response does not contain the soft deleted teachers.
        foreach ($deletedTeachers as $deletedTeacher) {
            $response->assertJsonMissing([
                'id' => $deletedTeacher->id,
            ]);
        }
    }

    public function test_non_admin_teachers_are_unauthorized_to_get_the_list_of_teachers(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

            $this->fakeNonAdminTeacher($school, 10);
        }

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route($this->routeName));

        // Assert that the request is unauthorized.
        $response->assertForbidden();
    }

    public function test_it_returns_limited_attributes_by_default()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $teacherAdmin = $this->fakeAdminTeacher($school);

            $this->fakeTeacher($school, 10);
        }

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->getJson(route($this->routeName));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'username',
                    'first_name',
                    'last_name',
                    'email',
                    'is_admin',
                ],
            ]
        ]);
    }

    public function test_it_returns_the_school_details_if_explicitly_requested()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $teacherAdmin = $this->fakeAdminTeacher($school);

            $this->fakeTeacher($school, 10);
        }

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->getJson(route($this->routeName, [
            'with_school' => true,
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'school' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ]);
    }

    public function test_it_returns_classrooms_details_if_explicitly_requested()
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $teacherAdmin = $this->fakeAdminTeacher($school);

            $this->fakeTeacher($school, 10);
        }

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->getJson(route($this->routeName, [
            'with_classrooms' => true,
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'owned_classrooms' => [
                            '*' => [
                                'id',
                                'name',
                                'pass_grade',
                            ],
                        ],
                        'secondary_classrooms' => [
                            '*' => [
                                'id',
                                'name',
                                'pass_grade',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_it_returns_fuzzy_search_results_by_teacher_names(): void
    {
        {
            $school = $this->fakeTraditionalSchool();

            $this->fakeSubscription($school);

            $teacher1 = $this->fakeAdminTeacher($school, 1, [
                'username' => 'gary',
                'first_name' => 'Gary',
                'last_name' => 'Zhang',
                'email' => 'gary@test.com',
            ]);

            $teacher2 = $this->fakeNonAdminTeacher($school, 1, [
                'username' => 'tom',
                'first_name' => 'Tom',
                'last_name' => 'Porter',
                'email' => 'tom.porter@mathsonline.com.au',
            ]);

            $teacher3 = $this->fakeNonAdminTeacher($school, 1, [
                'username' => 'mike',
                'first_name' => 'Tom',
                'last_name' => 'Smith',
                'email' => 'mike.smith@mathsonline.com.au',
            ]);
        }

        $this->actingAsTeacher($teacher1);

        $response = $this->getJson(route($this->routeName, ['search_key' => 'gar'])); // 'gar' is for 'Gary'

        $response->assertSuccessful();

        // Assert that the search result is correct
        $response->assertJsonFragment(['id' => $teacher1->id])
            ->assertJsonMissing(['id' => $teacher2->id])
            ->assertJsonMissing(['id' => $teacher3->id]);

        $response = $this->getJson(route($this->routeName, ['search_key' => 'to'])); // 'to' is for 'Tom'

        // Assert that the search result is correct
        $response->assertJsonMissing(['id' => $teacher1->id])
            ->assertJsonFragment(['id' => $teacher2->id])
            ->assertJsonFragment(['id' => $teacher3->id]);
    }
}
