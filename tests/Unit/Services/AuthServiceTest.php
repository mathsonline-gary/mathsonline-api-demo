<?php

namespace Tests\Unit\Services;

use App\Models\Users\Teacher;
use App\Services\AuthService;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * This testing class is used to test methods in AuthService.
 *
 * @see AuthService
 */
class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

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
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $teacher = $this->createTeacherAdmin($school);

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
        // TODO
    }

    /**
     * @see AuthService::tutor()
     */
    public function test_it_gets_current_authenticated_tutor()
    {
        // TODO
    }

    /**
     * @see AuthService::admin()
     */
    public function test_it_get_current_authenticated_admin()
    {
        // TODO
    }

    /**
     * @see AuthService::developer()
     */
    public function test_it_gets_current_authenticated_developer()
    {
        // TODO
    }
}
