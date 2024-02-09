<?php

namespace Tests\Helpers;

use App\Models\School;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

trait TeacherTestHelpers
{
    public function fakeTeacher(School $school = null, int $count = 1, array $attributes = []): Collection|Teacher
    {
        $school ??= $this->fakeTraditionalSchool();

        $teachers = Teacher::factory()
            ->count($count)
            ->create([
                ...$attributes,
                'school_id' => $school->id,
            ]);

        return $count === 1 ? $teachers->first() : $teachers;
    }

    /**
     * Create fake teacher(s) with admin access.
     *
     * @param School|null $school
     * @param int         $count
     * @param array       $attributes
     *
     * @return Collection<Teacher>|Teacher
     */
    public function fakeAdminTeacher(School $school = null, int $count = 1, array $attributes = []): Collection|Teacher
    {
        $school ??= $this->fakeTraditionalSchool();

        $teachers = Teacher::factory()
            ->count($count)
            ->admin()
            ->create([
                ...$attributes,
                'school_id' => $school->id,
            ]);

        return $count === 1 ? $teachers->first() : $teachers;
    }

    /**
     * Create non-admin teacher(s) in a given school.
     *
     * @param School|null $school
     * @param int         $count
     * @param array       $attributes
     *
     * @return Collection<Teacher>|Teacher
     */
    public function fakeNonAdminTeacher(School $school = null, int $count = 1, array $attributes = []): Collection|Teacher
    {
        $school ??= $this->fakeTraditionalSchool();

        $teachers = Teacher::factory()
            ->count($count)
            ->nonAdmin()
            ->create([
                ...$attributes,
                'school_id' => $school->id,
            ]);

        return $count === 1 ? $teachers->first() : $teachers;
    }

    /**
     * Set the currently logged-in teacher for the application.
     *
     * @param Teacher $teacher
     *
     * @return void
     */
    public function actingAsTeacher(Teacher $teacher): void
    {
        $this->actingAs($teacher->asUser());
    }

    /**
     * Assert that the teacher and the associated user has expected attributes.
     *
     * @param array   $expected
     * @param Teacher $teacher
     *
     * @return void
     */
    public function assertTeacherAttributes(array $expected, Teacher $teacher): void
    {
        if (count($expected) === 0) {
            return;
        }

        // Get the associated user.
        $user = $teacher->asUser();

        $this->assertEquals(User::TYPE_TEACHER, $user->type);
        $this->assertNull($user->email_verified_at);

        foreach ($expected as $attribute => $value) {
            switch ($attribute) {
                case 'user_id':
                    $this->assertEquals($value, $teacher->user_id);
                    $this->assertEquals($value, $user->id);
                    break;

                case 'username':
                    $this->assertEquals($value, $teacher->username);
                    $this->assertEquals($value, $user->login);
                    break;

                case 'email':
                    $this->assertEquals($value, $teacher->email);
                    $this->assertEquals($value, $user->email);
                    break;

                case 'password':
                    $this->assertTrue(Hash::check($value, $user->password));
                    break;

                case 'deleted_at':
                    if ($value === null) {
                        $this->assertNull($teacher->deleted_at);
                        $this->assertNull($user->deleted_at);
                        break;
                    }

                    $this->assertNotNull($teacher->deleted_at);
                    $this->assertNotNull($user->deleted_at);
                    break;

                default:
                    $this->assertEquals($value, $teacher->{$attribute});
                    break;
            }
        }
    }
}
