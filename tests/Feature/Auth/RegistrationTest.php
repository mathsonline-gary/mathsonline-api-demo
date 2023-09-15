<?php

namespace Tests\Feature\Auth;

use App\Models\Users\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register_as_a_member(): void
    {
        $payload = [
            'market_id' => 1,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '0411111111',
            'address_line_1' => 'Unit 101',
            'address_line_2' => '1 Test St',
            'address_city' => 'Sydney',
            'address_state' => 'NSW',
            'address_postal_code' => '2000',
            'address_country' => 'Australia',
        ];

        $response = $this->post(route('api.v1.register'), $payload);

        $newMember = Member::where('email', $payload['email'])->first();
        $newSchool = $newMember?->school;

        $response->assertStatus(201)
            ->assertJson([
                'token' => true,
            ]);

        $this->assertEquals(1, $newMember->tokens()->count());
        $this->assertNotNull($newMember);
        $this->assertNotNull($newSchool);
    }
}
