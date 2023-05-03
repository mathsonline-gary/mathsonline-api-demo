<?php

namespace Tests\Feature\Auth;

use App\Models\Users\Admin;
use App\Models\Users\Developer;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\Tutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_from_a_valid_referer(): void
    {
        $users = [
            'tutor' => Tutor::first(),
            'teacher' => Teacher::first(),
            'student' => Student::first(),
            'admin' => Admin::first(),
            'developer' => Developer::first(),
        ];

        foreach ($users as $role => $user) {
            $referer = "https://$role.test.com";

            $response = $this->withHeaders([
                'Referer' => $referer,
            ])->postJson(route('api.v1.login', [
                'email' => $user->email,
                'username' => $user->username,
                'password' => 'password',
                'role' => $role,
            ]));

            $response->assertStatus(201)
                ->assertJson([
                    'token' => true,
                ]);

            $this->assertEquals(1, $user->tokens()->count());
        }
    }

    public function test_users_cannot_authenticate_from_an_invalid_referer(): void
    {
        $users = [
            'tutor' => Tutor::first(),
            'teacher' => Teacher::first(),
            'student' => Student::first(),
            'admin' => Admin::first(),
            'developer' => Developer::first(),
        ];

        foreach ($users as $role => $user) {
            $referer = 'https://invalid.test.com';

            $response = $this->withHeaders([
                'Referer' => $referer,
            ])->postJson(route('api.v1.login', [
                'email' => $user->email,
                'username' => $user->username,
                'password' => 'password',
                'role' => $role,
            ]));

            $response->assertStatus(422);

            $this->assertEquals(0, $user->tokens()->count());
        }
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $users = [
            'tutor' => Tutor::first(),
            'teacher' => Teacher::first(),
            'student' => Student::first(),
            'admin' => Admin::first(),
            'developer' => Developer::first(),
        ];

        foreach ($users as $role => $user) {
            $referer = "https://$role.test.com";

            $response = $this->withHeaders([
                'Referer' => $referer,
            ])->postJson(route('api.v1.login', [
                'email' => $user->email,
                'username' => $user->username,
                'password' => 'incorrect-password',
                'role' => $role,
            ]));

            $response->assertStatus(422);

            $this->assertEquals(0, $user->tokens()->count());
        }
    }

    public function test_authenticated_users_can_logout()
    {
        $users = [
            'tutor' => Tutor::first(),
            'teacher' => Teacher::first(),
            'student' => Student::first(),
            'admin' => Admin::first(),
            'developer' => Developer::first(),
        ];

        foreach ($users as $user) {
            $token = $user->createToken('test_token')->plainTextToken;

            $this->actingAs($user);

            $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                ->post(route('api.v1.logout'));

            $response->assertStatus(204);
            $this->assertEquals(0, $user->tokens()->count());
        }
    }
}
