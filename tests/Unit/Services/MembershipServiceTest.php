<?php

namespace Tests\Unit\Services;

use App\Models\Membership;
use App\Services\MembershipService;
use Tests\TestCase;

class MembershipServiceTest extends TestCase
{
    /**
     * The membership service instance.
     *
     * @var MembershipService
     */
    protected MembershipService $membershipService;


    protected function setUp(): void
    {
        parent::setUp();

        $this->membershipService = new MembershipService();
    }

    /**
     * @see MembershipService::find()
     */
    public function test_it_finds_the_membership(): void
    {
        $this->assertDatabaseHas(Membership::class, ['id' => 1]);

        $membership = $this->membershipService->find(1);

        $this->assertInstanceOf(Membership::class, $membership);
        $this->assertEquals(1, $membership->id);
    }
}
