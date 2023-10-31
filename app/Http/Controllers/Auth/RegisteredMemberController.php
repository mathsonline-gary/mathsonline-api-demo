<?php

namespace App\Http\Controllers\Auth;

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
            'payment_source',
        ]);

        /** @var Member $member */
        $member = DB::transaction(function () use ($validated) {
            // Create a Stripe customer.
            $customer = $this->stripeService->createCustomer($validated);

            // Create a homeschool.
            $validated['type'] = SchoolType::HOMESCHOOL;
            $validated['stripe_customer_id'] = $customer->id;
            $validated['name'] = "Homeschool of {$validated['first_name']} {$validated['last_name']}";
            $school = $this->schoolService->create($validated);

            // Create a member.
            $validated['school_id'] = $school->id;

            return $this->memberService->create($validated);
        });

        // Log the member in.
        auth()->login($member->asUser());

        return $this->successResponse(
            data: new MemberResource($member),
            message: 'Member registered successfully.',
            status: 201,
        );
    }
}
