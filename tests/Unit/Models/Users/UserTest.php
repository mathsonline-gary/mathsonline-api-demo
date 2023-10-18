<?php

namespace Tests\Unit\Models\Users;


use App\Models\Users\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * @see User::isTeacher()
     */
    public function test_it_knows_the_user_is_a_teacher(): void
    {
        $teacher = $this->fakeTeacher();
        $user = User::find($teacher->user_id);

        $this->assertTrue($user->isTeacher());
    }

    /**
     * @see User::asTeacher()
     */
    public function test_it_returns_a_teacher(): void
    {
        $teacher = $this->fakeTeacher();
        $user = User::find($teacher->user_id);

        $this->assertEquals($teacher->id, $user->asTeacher()->id);
    }

    /**
     * @see User::asTeacher()
     */
    public function test_it_returns_null_when_the_teacher_does_not_exist(): void
    {
        $user = User::factory()->teacher()->create();

        $this->assertNull($user->asTeacher());
    }

    /**
     * @see User::asTeacher()
     */
    public function test_it_returns_null_when_the_user_is_not_a_teacher(): void
    {
        // TODO: Implement test_it_returns_null_when_the_user_is_not_a_teacher() method.
        $this->assertTrue(true);
    }
}
