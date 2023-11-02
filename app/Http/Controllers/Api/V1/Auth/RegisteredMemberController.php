<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\SchoolType;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Auth\RegisterMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Users\Member;
use App\Services\MemberService;
use App\Services\SchoolService;
use App\Services\StripeService;
use Illuminate\Support\Facades\DB;

class RegisteredMemberController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected SchoolService $schoolService,
        protected MemberService $memberService,
    )
    {
    }

    public function store(RegisterMemberRequest $request)
    {
        $validated = $request->safe()->only([
            'market_id',
            'first_name',
            'last_name',
            'email',
            'password',
            'phone',
            'address_line_1',
            'address_line_2',
            'address_city',
            'address_state',
            'address_postal_code',
            'address_country',
            'payment_method',
        ]);

        /** @var Member $member */
        $member = DB::transaction(function () use ($validated) {
            // Create a Stripe customer.
            $customer = $this->stripeService->createCustomer($validated);

            // Create a homeschool.
            $school = $this->schoolService->create([
                ...$validated,
                'type' => SchoolType::HOMESCHOOL,
                'stripe_customer_id' => $customer->id,
                'name' => "Homeschool of {$validated['first_name']} {$validated['last_name']}",
            ]);

            // Create a member.
            return $this->memberService->create([
                ...$validated,
                'school_id' => $school->id,
            ]);
        });

        // Create an API token for the member.
        $token = $member->asUser()->createToken('member-registration')->plainTextToken;

        return $this->successResponse(
            data: [
                'member' => new MemberResource($member),
                'token' => $token,
            ],
            message: 'Member registered successfully.',
            status: 201,
        );
    }
}
