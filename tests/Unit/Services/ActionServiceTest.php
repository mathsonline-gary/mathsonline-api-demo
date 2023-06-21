<?php

namespace Tests\Unit\Services;

use App\Enums\ActionTypes;
use App\Services\ActionService;
use Carbon\Carbon;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * This testing class is used to test methods in ActionService.
 *
 * @see ActionService
 */
class ActionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ActionService $actionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actionService = new ActionService();
    }

    /**
     * @return void
     *
     * @see ActionService::create()
     */
    public function test_it_creates_actions()
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->createTraditionalSchool();
        $teacher = $this->createTeacherAdmin($school);

        $type = ActionTypes::LOG_IN;
        $actedAt = Carbon::now();
        $data = ['key' => 'value'];

        $this->actionService->create($teacher, $type, $actedAt, $data);

        // Assert that an action was created.
        $this->assertDatabaseCount('actions', 1);

        // Assert that the created action is correct.
        $this->assertCount(1, $teacher->actions);
        $this->assertEquals($type, $teacher->actions->first()->action);
        $this->assertEquals($actedAt->timestamp, $teacher->actions->first()->acted_at->timestamp);
        $this->assertEquals($data, $teacher->actions->first()->data);
    }
}
