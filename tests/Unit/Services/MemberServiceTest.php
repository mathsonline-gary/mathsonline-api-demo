<?php

namespace Tests\Unit\Services;

use App\Enums\UserType;
use App\Models\Users\Member;
use App\Models\Users\User;
use App\Services\MemberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The member service instance.
     *
     * @var MemberService
     */
    protected MemberService $memberService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->memberService = new MemberService();
    }

    /**
     * @see MemberService::create()
     */
    public function test_it_creates_the_member(): void
    {
        $school = $this->fakeHomeschool();

        $attributes = [
            'school_id' => $school->id,
            'email' => fake()->safeEmail,
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'password' => 'password',
        ];

        $member = $this->memberService->create($attributes);

        // Assert that it created the user correctly.
        $this->assertDatabaseCount('users', 1);
        $user = User::first();
        $this->assertEquals($attributes['email'], $user->login);
        $this->assertTrue(Hash::check($attributes['password'], $user->password));
        $this->assertEquals(UserType::MEMBER, $user->type);
        $this->assertNull($user->oauth_google_id);
        $this->assertNull($user->deleted_at);

        // Assert that the member was created correctly in the database.
        $this->assertDatabaseCount('members', 1);
        $this->assertDatabaseHas('members', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'email' => $attributes['email'],
            'first_name' => $attributes['first_name'],
            'last_name' => $attributes['last_name'],
        ]);

        // Assert that it returns the correct member.
        $this->assertInstanceOf(Member::class, $member);
        $this->assertEquals($user->id, $member->user_id);
        $this->assertEquals($school->id, $member->school_id);
        $this->assertEquals($attributes['email'], $member->email);
        $this->assertEquals($attributes['first_name'], $member->first_name);
        $this->assertEquals($attributes['last_name'], $member->last_name);
    }
}
