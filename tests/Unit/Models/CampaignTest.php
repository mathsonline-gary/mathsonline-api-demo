<?php

namespace Tests\Unit\Models;

use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @see Campaign::isActive()
     */
    public function test_it_indicates_whether_it_is_active(): void
    {
        $campaign = Campaign::factory()->create([
            'expires_at' => null,
        ]);

        // Assert that the campaign is active
        $this->assertTrue($campaign->isActive());

        $campaign = Campaign::factory()->create([
            'expires_at' => now()->addYear(),
        ]);

        // Assert that the campaign is active
        $this->assertTrue($campaign->isActive());

        $campaign = Campaign::factory()->create([
            'expires_at' => now()->subYear(),
        ]);

        // Assert that the campaign is not active
        $this->assertFalse($campaign->isActive());
    }
}
