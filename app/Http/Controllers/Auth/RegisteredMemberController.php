<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Auth\RegisterMemberRequest;
use App\Models\School;
use App\Models\Users\Member;
use App\Services\MemberService;
use App\Services\SchoolService;
use App\Services\StripeService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

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
        ]);

        try {
            /** @var Member $member */
            $member = DB::transaction(function () use ($validated) {
                // Create a Stripe customer.
                $customer = $this->stripeService->createCustomer($validated);

                // Create a homeschool.
                $school = $this->schoolService->create([
                    ...$validated,
                    'type' => School::TYPE_HOMESCHOOL,
                    'stripe_id' => $customer->id,
                    'name' => "Homeschool of {$validated['first_name']} {$validated['last_name']}",
                ]);

                // Create a member.
                return $this->memberService->create([
                    ...$validated,
                    'school_id' => $school->id,
                ]);
            });
        } catch (Throwable) {
            return $this->errorResponse(
                message: 'An error occurred while registering the member.',
                status: 500
            );
        }

        $user = $member->asUser();

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
