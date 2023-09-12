<?php

namespace Tests\Unit\Services;

use App\Models\Market;
use App\Models\School;
use App\Services\SchoolService;
use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * This testing class is used to test methods in SchoolService.
 *
 * @see SchoolService
 */
class SchoolServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SchoolService $schoolService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schoolService = new SchoolService();
    }

    /**
     * @return void
     *
     * @see SchoolService::create()
     */
    public function test_it_creates_a_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $attributes = [
            'market_id' => Market::first()->id,
            'name' => 'Test School',
            'type' => School::TYPE_TRADITIONAL_SCHOOL,
            'email' => 'school@test.com',
            'phone' => '1234567890',
            'fax' => '9876543210',
            'address_line_1' => '123 Main Street',
            'address_line_2' => 'Suite 100',
            'address_city' => 'Sydney',
            'address_state' => 'NSW',
            'address_postal_code' => '2000',
            'address_country' => 'Australia',
        ];

        // Enable logger.
        Log::shouldReceive('info')->once();

        $school = $this->schoolService->create($attributes);

        // Assert that the school was created correctly.
        $this->assertDatabaseCount('schools', 1);
        $this->assertDatabaseHas('schools', $attributes);
        $this->assertInstanceOf(School::class, $school);
    }
}
