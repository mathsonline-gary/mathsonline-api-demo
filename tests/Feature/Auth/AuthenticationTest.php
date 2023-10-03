<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_using_the_login_screen(): void
    {

    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $this->assertGuest();
    }
}
