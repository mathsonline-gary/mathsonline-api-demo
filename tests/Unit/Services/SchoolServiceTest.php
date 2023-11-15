<?php

namespace Tests\Unit\Services;

use App\Enums\SchoolType;
use App\Models\Market;
use App\Models\School;
use App\Services\SchoolService;
use Tests\TestCase;

/**
 * This testing class is used to test methods in SchoolService.
 *
 * @see SchoolService
 */
class SchoolServiceTest extends TestCase
{
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
        $attributes = [
            'market_id' => Market::first()->id,
            'name' => 'Test School',
            'type' => SchoolType::TRADITIONAL_SCHOOL,
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

        $school = $this->schoolService->create($attributes);

        // Assert that the school was created correctly.
        $this->assertDatabaseCount('schools', 1);
        $this->assertDatabaseHas('schools', $attributes);
        $this->assertInstanceOf(School::class, $school);
    }
}
