<?php

namespace Tests\Unit\Services;

use App\Enums\ActivityType;
use App\Services\ActivityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * This testing class is used to test methods in ActivityService.
 *
 * @see ActivityService
 */
class ActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ActivityService $activityService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activityService = new ActivityService();
    }

    /**
     * @return void
     *
     * @see ActivityService::create()
     */
    public function test_it_creates_activities()
    {
        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);
        $user = $teacher->asUser();

        $type = ActivityType::LOGGED_IN;
        $actedAt = Carbon::now();
        $data = ['key' => 'value'];

        $this->activityService->create($user, $type, $actedAt, $data);

        // Assert that an activity was created.
        $this->assertDatabaseCount('activities', 1);

        // Assert that the created activity is correct.
        $this->assertCount(1, $user->activities);
        $this->assertEquals($type, $user->activities->first()->type);
        $this->assertEquals($actedAt->timestamp, $user->activities->first()->acted_at->timestamp);
        $this->assertEquals($data, $user->activities->first()->data);
    }
}
