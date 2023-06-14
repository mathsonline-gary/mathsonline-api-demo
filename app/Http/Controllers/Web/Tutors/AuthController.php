<?php

namespace App\Http\Controllers\Web\Tutors;

use App\Http\Controllers\Web\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Users\Tutor;
use App\Services\AuthService;
use App\Services\SchoolService;
use App\Services\TutorService;
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
        protected TutorService  $tutorService
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
            DB::transaction(function () use ($validated, $request, &$tutor) {
                $school = $this->schoolService->create([
                    ...$validated,
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'] . "'s Homeschool",
                    'type' => 'homeschool',
                ]);

                $tutor = $this->tutorService->create([
                    ...$validated,
                    'type_id' => 1,
                    'school_id' => $school->id,
                ]);

                event(new Registered($tutor));

                Auth::login($tutor);
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
        $tutor = $request->user();

        if ($tutor instanceof Tutor) {
            return response()->json([
                'user' => $tutor,
                'type' => 'tutor',
            ]);
        }

        abort(401);
    }
}
