<?php

namespace Tests\Traits;

use App\Models\School;
use App\Models\Users\Member;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

trait MemberTestHelpers
{
    /**
     * Fake a member.
     *
     * @param int   $marketId
     * @param array $attributes
     *
     * @return Member
     *
     * @throws ApiErrorException
     */
    public function fakeMember(int $marketId = 1, array $attributes = []): Member
    {
        // Fake a homeschool.
        $school = School::factory()->make([
            ...$attributes,
            'market_id' => $marketId,
            'type' => School::TYPE_HOMESCHOOL,
        ]);

        // Fake a member.
        $member = Member::factory()->make([
            ...$attributes,
            'email' => $school->email,
        ]);

        // Create a Stripe customer.
        $stripeClient = new StripeClient(config("services.stripe.$marketId.secret"));

        $stripeCustomer = $stripeClient->customers->create([
            'name' => "PHPUnit Test Customer",
            'email' => $school->email,
            'phone' => $school->phone,
            'address' => [
                'line1' => $school->address_line_1,
                'line2' => $school->address_line_2,
                'city' => $school->address_city,
                'state' => $school->address_state,
                'postal_code' => $school->address_postal_code,
                'country' => $school->address_country,
            ],
            'source' => 'tok_visa',
        ]);

        // Save the school.
        $school->stripe_id = $stripeCustomer->id;
        $school->save();

        // Save the member.
        $member->school_id = $school->id;
        $member->save();

        return $member->refresh();
    }

    /**
     * Set the currently logged-in member for the application.
     *
     * @param mixed $member
     *
     * @return void
     */
    public function actingAsMember(mixed $member): void
    {
        $this->actingAs($member->asUser());
    }
}
