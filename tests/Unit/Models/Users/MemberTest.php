<?php

namespace Tests\Unit\Models\Users;

use App\Models\School;
use App\Models\Users\Member;
use Database\Seeders\MarketSeeder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_belongs_to_a_school(): void
    {
        $this->seed([
            MarketSeeder::class,
        ]);

        $school = School::factory()
            ->homeschool()
            ->create();

        $member = Member::factory()
            ->ofSchool($school)
            ->create();

        // Assert that the member has a relationship with the school
        $this->assertInstanceOf(BelongsTo::class, $member->school());

        // Assert that the member's school is an instance of School
        $this->assertInstanceOf(School::class, $member->school);

        // Assert that the member is associated with the correct school
        $this->assertEquals($member->school->id, $school->id);
    }
}
