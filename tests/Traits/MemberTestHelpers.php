<?php

namespace Tests\Traits;

use App\Enums\SchoolType;
use App\Models\Users\Member;
use App\Services\MemberService;
use App\Services\SchoolService;

trait MemberTestHelpers
{
    /**
     * Fake a member.
     *
     * @param int $marketId
     * @param array $attributes
     * @return Member
     */
    public function fakeMember(int $marketId = 1, array $attributes = []): Member
    {
        $attributes = [
            'market_id' => $marketId,
            'email' => $attributes['email'] ?? fake()->safeEmail,
            'password' => $attributes['password'] ?? 'password',
            'first_name' => $attributes['first_name'] ?? fake()->firstName,
            'last_name' => $attributes['last_name'] ?? fake()->lastName,
            'phone' => $attributes['phone'] ?? fake()->phoneNumber,
            'address_line_1' => $attributes['address_line_1'] ?? fake()->streetAddress,
            'address_line_2' => $attributes['address_line_2'] ?? null,
            'address_city' => $attributes['address_city'] ?? fake()->city,
            'address_state' => $attributes['address_state'] ?? fake()->city,
            'address_postal_code' => $attributes['address_postal_code'] ?? fake()->postcode,
            'address_country' => $attributes['address_country'] ?? fake()->country,
        ];

        // Create a homeschool.
        $schoolService = new SchoolService();

        $school = $schoolService->create([
            ... $attributes,
            'type' => SchoolType::HOMESCHOOL->value,
            'stripe_customer_id' => 'cus_' . fake()->uuid,
            'name' => "Homeschool of {$attributes['first_name']} {$attributes['last_name']}",
        ]);

        // Create a member.
        $memberService = new MemberService();

        return $memberService->create([
            ...$attributes,
            'school_id' => $school->id,
        ]);
    }

    /**
     * Set the currently logged-in member for the application.
     *
     * @param mixed $member
     * @return void
     */
    public function actingAsMember(mixed $member): void
    {
        $this->actingAs($member->asUser());
    }
}
