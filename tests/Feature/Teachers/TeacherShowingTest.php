<?php

namespace Tests\Feature\Teachers;

use App\Models\School;
use App\Models\Users\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherShowingTest extends TestCase
{
    use RefreshDatabase;

    public function test_teachers_with_administrator_access_can_get_the_profile_of_a_teacher_in_same_school(): void
    {
        // Create a test school.
        $school = School::factory()
            ->traditionalSchool()
            ->create();

        // Create a test teacher admin in the school.
        $teacherAdmin = Teacher::factory()
            ->admin()
            ->create([
                'school_id' => $school->id,
            ]);

        // Create a test teacher in the school.
        $teacher = Teacher::factory()
            ->create([
                'school_id' => $school->id,
            ]);

        // Authenticate as the teacher admin.
        $this->actingAs($teacherAdmin);

        // Send request
        $response = $this->get(route('api.teachers.v1.teachers.show', $teacher->id));

        // Assert that the request is successful.
        $response->assertStatus(200);

        // Assert that the teacher profile is correct.
        $response->assertJson([
            'id' => $teacher->id,
        ]);
    }

    public function test_teachers_without_administrator_access_are_unauthorised_to_get_the_profile_of_another_teacher_in_same_school(): void
    {
        // Create a test school.
        $school = School::factory()
            ->traditionalSchool()
            ->create();

        // Create two non-admin teachers.
        $teacher1 = Teacher::factory()
            ->create([
                'school_id' => $school->id,
            ]);

        $teacher2 = Teacher::factory()
            ->create([
                'school_id' => $school->id,
            ]);

        // Authenticate as a non-admin teacher.
        $this->actingAs($teacher1);

        // Send request.
        $response = $this->get(route('api.teachers.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertStatus(403);
    }

    public function test_teachers_with_administrator_access_are_unauthorized_to_get_the_profile_of_a_teacher_in_different_school(): void
    {
        // Create a test school 1.
        $school1 = School::factory()
            ->traditionalSchool()
            ->create();

        // Create a teacher admin in school 1.
        $teacher1 = Teacher::factory()
            ->admin()
            ->create([
                'school_id' => $school1->id,
            ]);

        // Create a school 2.
        $school2 = School::factory()
            ->traditionalSchool()
            ->create();

        // Create a teacher in school 2.
        $teacher2 = Teacher::factory()
            ->create([
                'school_id' => $school2->id,
            ]);

        // Authenticate as teacher admin in school 1.
        $this->actingAs($teacher1);

        // Send request to get the teacher profile in school 2.
        $response = $this->get(route('api.teachers.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertStatus(403);
    }

    public function test_teachers_without_administrator_access_are_unauthorised_to_get_the_profile_of_a_teacher_in_different_school(): void
    {
        // Create a test school 1.
        $school1 = School::factory()
            ->traditionalSchool()
            ->create();

        // Create a non-admin teacher in school 1.
        $teacher1 = Teacher::factory()
            ->create([
                'school_id' => $school1->id,
            ]);

        // Create a school 2.
        $school2 = School::factory()
            ->traditionalSchool()
            ->create();

        // Create a teacher in school 2.
        $teacher2 = Teacher::factory()
            ->create([
                'school_id' => $school2->id,
            ]);

        // Authenticate as teacher admin in school 1.
        $this->actingAs($teacher1);

        // Send request to get the teacher profile in school 2.
        $response = $this->get(route('api.teachers.v1.teachers.show', $teacher2->id));

        // Assert that the request is unauthorized.
        $response->assertStatus(403);
    }
}
