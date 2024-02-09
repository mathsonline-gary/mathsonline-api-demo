<?php

namespace Tests\Helpers;

use App\Models\School;
use App\Models\Users\Student;
use App\Models\Users\StudentSetting;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

trait StudentTestHelpers
{
    /**
     * Create student(s) in the given school.
     *
     * @param School|null $school
     * @param int         $count
     * @param array       $attributes
     *
     * @return Collection|Student
     */
    public function fakeStudent(School $school = null, int $count = 1, array $attributes = []): Collection|Student
    {
        $school ??= $this->fakeSchool();

        $students = Student::factory()
            ->count($count)
            ->has(
                StudentSetting::factory()
                    ->count(1),
                'settings'
            )
            ->create([
                ...$attributes,
                'school_id' => $school->id,
            ]);

        return $count === 1 ? $students->first() : $students;
    }

    /**
     * Set the currently logged-in student for the application.
     *
     * @param Student $student
     *
     * @return void
     */
    public function actingAsStudent(Student $student): void
    {
        $this->actingAs($student->asUser());
    }

    /**
     * Assert that the given student has the expected attributes.
     *
     * @param array   $expected
     * @param Student $student
     *
     * @return void
     */
    public function assertStudentAttributes(array $expected, Student $student): void
    {
        // Get the associated user.
        $user = $student->asUser();

        $this->assertEquals(User::TYPE_STUDENT, $user->type);
        $this->assertNull($user->email_verified_at);

        // Get the student settings.
        $settings = $student->settings;

        foreach ($expected as $attribute => $value) {
            switch ($attribute) {
                case 'user_id':
                    $this->assertEquals($value, $student->user_id);
                    $this->assertEquals($value, $user->id);
                    break;

                case 'username':
                    $this->assertEquals($value, $student->username);
                    $this->assertEquals($value, $user->login);
                    break;

                case 'email':
                    $this->assertEquals($value, $student->email);
                    $this->assertEquals($value, $user->email);
                    break;

                case 'password':
                    $this->assertTrue(Hash::check($value, $user->password));
                    break;

                case 'deleted_at':
                    if ($value === null) {
                        $this->assertNull($student->deleted_at);
                        $this->assertNull($user->deleted_at);
                        break;
                    }

                    $this->assertNotNull($student->deleted_at);
                    $this->assertNotNull($user->deleted_at);
                    break;

                case 'settings':
                    $this->assertStudentSettingsAttributes($value, $settings);
                    break;

                default:
                    $this->assertEquals($value, $student->{$attribute});
                    break;
            }
        }
    }

    /**
     * Assert that the given student settings have the expected attributes.
     *
     * @param array          $expected
     * @param StudentSetting $settings
     *
     * @return void
     */
    public function assertStudentSettingsAttributes(array $expected, StudentSetting $settings): void
    {
        foreach ($expected as $attribute => $value) {
            $this->assertEquals($value, $settings->{$attribute});
        }
    }

}
