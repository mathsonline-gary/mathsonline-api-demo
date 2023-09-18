<?php

namespace App\Http\Controllers\Web\Members;

use App\Http\Controllers\Web\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Users\Member;
use App\Services\AuthService;
use App\Services\SchoolService;
use App\Services\MemberService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService   $authService,
        protected SchoolService $schoolService,
        protected MemberService $memberService
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function register(RegisterRequest $request)
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
            'address_country'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $school = $this->schoolService->create([
                    ...$validated,
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'] . "'s Homeschool",
                    'type' => 'homeschool',
                ]);

                $member = $this->memberService->create([
                    ...$validated,
                    'type_id' => 1,
                    'school_id' => $school->id,
                ]);

                event(new Registered($member));

                Auth::login($member);
            });
        } catch (Throwable $exception) {
            Log::error('Failed to register: ', [
                ...Arr::only($validated, [
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                ]),
                'exception' => $exception->getMessage(),
            ]);

            // TODO: Mail to account manager about the failure.

            throw $exception;
        }

        return response()->noContent();
    }

    public function login(LoginRequest $request): Response
    {
        $this->authService->login($request);

        return response()->noContent();
    }

    public function logout(Request $request): Response
    {
        $this->authService->logout($request);

        return response()->noContent();
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = $this->authService->sendPasswordResetLink($request->only('email'));

        return response()->json(['status' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $status = $this->resetPassword($request);

        return response()->json(['status' => __($status)]);
    }

    public function me(Request $request)
    {
        $member = $request->user();

        if ($member instanceof Member) {
            return response()->json([
                'user' => $member,
                'type' => 'member',
            ]);
        }

        abort(401);
    }
}
