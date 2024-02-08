<?php

namespace Tests\Unit\Services;

use App\Models\Activity;
use App\Services\ActivityService;
use Tests\TestCase;

/**
 * This testing class is used to test methods in ActivityService.
 *
 * @see ActivityService
 */
class ActivityServiceTest extends TestCase
{
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

        $activity = Activity::factory()->make();

        $this->activityService->create($user, $activity->type, $activity->description, $activity->acted_at, $activity->data);

        // Assert that an activity was created.
        $this->assertDatabaseCount('activities', 1);

        // Assert that the created activity is correct.
        $this->assertCount(1, $user->activities);
        $this->assertEquals($activity->type, $user->activities->first()->type);
        $this->assertEquals($activity->acted_at->timestamp, $user->activities->first()->acted_at->timestamp);
        $this->assertEquals($activity->data, $user->activities->first()->data);
    }
}
