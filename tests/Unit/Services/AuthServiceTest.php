<?php

namespace Tests\Unit\Services;

use App\Models\Users\Member;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Services\AuthService;
use Exception;
use Tests\TestCase;

/**
 * This testing class is used to test methods in AuthService.
 *
 * @see AuthService
 */
class AuthServiceTest extends TestCase
{
    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = new AuthService();
    }

    /**
     * @see AuthService::teacher()
     */
    public function test_it_gets_current_authenticated_teacher()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->actingAsTeacher($teacher);

        $authenticatedUser = $this->authService->teacher();

        $this->assertInstanceOf(Teacher::class, $authenticatedUser);
        $this->assertEquals($teacher->id, $authenticatedUser->id);
    }

    /**
     * @see AuthService::student()
     */
    public function test_it_gets_current_authenticated_student()
    {
        $school = $this->fakeSchool();
        $student = $this->fakeStudent($school);

        $this->actingAsStudent($student);

        $authenticatedUser = $this->authService->student();

        $this->assertInstanceOf(Student::class, $authenticatedUser);
        $this->assertEquals($student->id, $authenticatedUser->id);
    }

    /**
     * @see AuthService::member()
     */
    public function test_it_gets_current_authenticated_member()
    {
        try {
            $member = $this->fakeMember();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->actingAsMember($member);

        $authenticatedUser = $this->authService->member();

        $this->assertInstanceOf(Member::class, $authenticatedUser);
        $this->assertEquals($member->id, $authenticatedUser->id);
    }

    /**
     * @see AuthService::admin()
     */
    public function test_it_get_current_authenticated_admin()
    {
        // TODO
        $this->assertTrue(true);
    }

    /**
     * @see AuthService::developer()
     */
    public function test_it_gets_current_authenticated_developer()
    {
        // TODO
        $this->assertTrue(true);
    }
}
