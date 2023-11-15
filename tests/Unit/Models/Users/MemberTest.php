<?php

namespace Tests\Unit\Models\Users;

use App\Models\School;
use App\Models\Users\Member;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class MemberTest extends TestCase
{
    public function test_a_member_belongs_to_a_homeschool(): void
    {
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
