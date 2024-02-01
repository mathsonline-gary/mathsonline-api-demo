<?php

namespace Tests\Feature\Auth;

use App\Models\School;
use App\Models\Users\Member;
use App\Models\Users\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Stripe\StripeClient;
use Tests\TestCase;

class MemberRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The payload to be used for registration.
     *
     * @var array
     */
    protected array $payload;

    /**
     * The Stripe client to be used for testing.
     *
     * @var StripeClient
     */
    protected StripeClient $stripeClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payload = [
            'market_id' => 1,
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'email' => fake()->safeEmail,
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => fake()->phoneNumber,
            'address_line_1' => fake()->buildingNumber,
            'address_line_2' => fake()->streetAddress,
            'address_city' => 'Sydney',
            'address_state' => 'NSW',
            'address_postal_code' => '2000',
            'address_country' => 'Australia',
        ];

        $secretKey = config("services.stripe.{$this->payload['market_id']}.secret");

        $this->stripeClient = new StripeClient($secretKey);
    }

    public function test_new_users_can_register_as_a_member(): void
    {
        $this->assertGuest();

        $response = $this->post(route('register.member'), $this->payload);

        $response->assertNoContent();
    }

    public function test_it_registers_the_member()
    {
        $this->assertGuest();

        $schoolCount = School::count();
        $userCount = User::count();
        $memberCount = Member::count();

        $response = $this->post(route('register.member'), $this->payload);

        $response->assertNoContent();

        // Assert that the school was created correctly.
        $this->assertDatabaseCount('schools', $schoolCount + 1);
        $school = School::latest()->first();
        $this->assertEquals($this->payload['market_id'], $school->market_id);
        $this->assertEquals("Homeschool of {$this->payload['first_name']} {$this->payload['last_name']}", $school->name);
        $this->assertEquals(School::TYPE_HOMESCHOOL, $school->type);
        $this->assertEquals($this->payload['email'], $school->email);
        $this->assertEquals($this->payload['phone'], $school->phone);
        $this->assertEquals($this->payload['address_line_1'], $school->address_line_1);
        $this->assertEquals($this->payload['address_line_2'], $school->address_line_2);
        $this->assertEquals($this->payload['address_city'], $school->address_city);
        $this->assertEquals($this->payload['address_state'], $school->address_state);
        $this->assertEquals($this->payload['address_postal_code'], $school->address_postal_code);
        $this->assertEquals($this->payload['address_country'], $school->address_country);
        $this->assertNotNull($school->stripe_id);

        // Assert that the Stripe customer was created correctly.
        $stripeCustomer = $this->stripeClient->customers->retrieve($school->stripe_id);
        $this->assertEquals($this->payload['email'], $stripeCustomer->email);
        $this->assertEquals("{$this->payload['first_name']} {$this->payload['last_name']}", $stripeCustomer->name);
        $this->assertEquals($this->payload['phone'], $stripeCustomer->phone);
        $this->assertEquals($this->payload['email'], $stripeCustomer->email);
        $this->assertEquals($this->payload['address_line_1'], $stripeCustomer->address->line1);
        $this->assertEquals($this->payload['address_line_2'], $stripeCustomer->address->line2);
        $this->assertEquals($this->payload['address_city'], $stripeCustomer->address->city);
        $this->assertEquals($this->payload['address_state'], $stripeCustomer->address->state);
        $this->assertEquals($this->payload['address_postal_code'], $stripeCustomer->address->postal_code);
        $this->assertEquals($this->payload['address_country'], $stripeCustomer->address->country);

        // Assert that the user was created correctly.
        $this->assertDatabaseCount('users', $userCount + 1);
        $user = User::latest('id')->first();
        $this->assertEquals($this->payload['email'], $user->login);
        $this->assertTrue(Hash::check($this->payload['password'], $user->password));
        $this->assertEquals(User::TYPE_MEMBER, $user->type);

        // Assert that the member was created correctly.
        $this->assertDatabaseCount('members', $memberCount + 1);
        $member = Member::latest()->first();
        $this->assertEquals($user->id, $member->user_id);
        $this->assertEquals($school->id, $member->school_id);
        $this->assertEquals($this->payload['first_name'], $member->first_name);
        $this->assertEquals($this->payload['last_name'], $member->last_name);
        $this->assertEquals($this->payload['email'], $member->email);
    }

    public function test_it_logs_the_member_in_after_registration()
    {
        $this->assertGuest();

        $response = $this->post(route('register.member'), $this->payload);

        $response->assertNoContent();

        $this->assertAuthenticatedAs(User::latest('id')->first());
    }

    public function test_it_send_email_verification_to_the_member()
    {
        Notification::fake();

        $this->assertGuest();

        $this->post(route('register.member'), $this->payload);

        Notification::assertSentTo(
            User::latest('id')->first(),
            VerifyEmail::class
        );
    }
}
