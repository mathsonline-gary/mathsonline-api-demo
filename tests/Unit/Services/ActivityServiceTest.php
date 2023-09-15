<?php

namespace Tests\Unit\Services;

use App\Enums\ActivityTypes;
use App\Services\ActivityService;
use Carbon\Carbon;
use Database\Seeders\MarketSeeder;
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

        $type = ActivityTypes::LOGGED_IN;
        $actedAt = Carbon::now();
        $data = ['key' => 'value'];

        $this->activityService->create($teacher, $type, $actedAt, $data);

        // Assert that an activity was created.
        $this->assertDatabaseCount('activities', 1);

        // Assert that the created activity is correct.
        $this->assertCount(1, $teacher->activities);
        $this->assertEquals($type, $teacher->activities->first()->type);
        $this->assertEquals($actedAt->timestamp, $teacher->activities->first()->acted_at->timestamp);
        $this->assertEquals($data, $teacher->activities->first()->data);
    }
}
