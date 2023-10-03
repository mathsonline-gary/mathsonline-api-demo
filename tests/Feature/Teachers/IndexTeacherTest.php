<?php

namespace Feature\Teachers;

use App\Http\Controllers\Api\V1\TeacherController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test teacher indexing endpoint for teachers.
 *
 * @see /routes/api/api-teachers.php
 * @see TeacherController::index()
 */
class IndexTeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_teachers_can_only_get_the_list_of_teachers_in_same_school(): void
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $teacherAdmin = $this->fakeAdminTeacher($school1);
        $teachers1 = $this->fakeNonAdminTeacher($school1, 10);
        $this->fakeNonAdminTeacher($school2, 10);

        $this->actingAsTeacher($teacherAdmin);

        $response = $this->getJson(route('api.teachers.v1.teachers.index'));

        // Assert that the request is successful.
        $response->assertOk();

        // Assert that the response contains the correct number of teachers.
        $response->assertJsonCount($teachers1->count() + 1, 'data');

        // Assert the response has the expected attributes of each teacher.
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'username',
                    'first_name',
                    'last_name',
                    'is_admin',
                ],
            ]
        ]);

        // Assert the response does not contain the password of each teacher.
        $response->assertJsonMissingPath('data.*.password');

        // Assert all teachers in school 2 are not included.
        $response->assertJsonMissing([
            'school_id' => $school2->id,
        ]);
    }

    public function test_non_admin_teachers_are_unauthorised_to_get_the_list_of_teachers(): void
    {
        $school = $this->fakeTraditionalSchool();

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $this->fakeNonAdminTeacher($school, 10);

        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.teachers.index'));

        // Assert that the request is unauthorised.
        $response->assertForbidden();
    }

    public function test_admin_teachers_can_fuzzy_search_teachers_by_names(): void
    {
        $school = $this->fakeTraditionalSchool();

        $teacher1 = $this->fakeAdminTeacher($school, 1, [
            'username' => 'gary',
            'first_name' => 'Gary',
            'last_name' => 'Zhang',
            'email' => 'gary@test.com'
        ]);

        $teacher2 = $this->fakeNonAdminTeacher($school, 1, [
            'username' => 'tom',
            'first_name' => 'Tom',
            'last_name' => 'Porter',
            'email' => 'tom.porter@mathsonline.com.au'
        ]);

        $teacher3 = $this->fakeNonAdminTeacher($school, 1, [
            'username' => 'mike',
            'first_name' => 'Tom',
            'last_name' => 'Smith',
            'email' => 'mike.smith@mathsonline.com.au'
        ]);

        $this->actingAsTeacher($teacher1);

        $response = $this->getJson(route('api.teachers.v1.teachers.index', ['search_key' => 'gar'])); // 'gar' is for 'Gary'

        $response->assertSuccessful();

        // Assert that the search result is correct
        $response->assertJsonFragment(['id' => $teacher1->id])
            ->assertJsonMissing(['id' => $teacher2->id])
            ->assertJsonMissing(['id' => $teacher3->id]);

        $response = $this->getJson(route('api.teachers.v1.teachers.index', ['search_key' => 'to'])); // 'to' is for 'Tom'

        // Assert that the search result is correct
        $response->assertJsonMissing(['id' => $teacher1->id])
            ->assertJsonFragment(['id' => $teacher2->id])
            ->assertJsonFragment(['id' => $teacher3->id]);
    }

    public function test_non_admin_teachers_are_unauthorized_to_search_teachers()
    {
        $school = $this->fakeTraditionalSchool();
        $teachers = $this->fakeNonAdminTeacher($school, 10);

        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);
        $this->actingAsTeacher($nonAdminTeacher);

        $response = $this->getJson(route('api.teachers.v1.teachers.index', ['search_key' => 'gary'])); // 'gar' is for 'Gary'

        $response->assertForbidden();
    }
}
