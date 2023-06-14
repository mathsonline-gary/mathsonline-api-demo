<?php

namespace App\Http\Controllers\Web\Teachers;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Http\Controllers\Web\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Activity;
use App\Models\Users\Teacher;
use App\Services\ActivityService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService     $authService,
        protected ActivityService $activityService,
    )
    {
    }

    public function login(LoginRequest $request): Response
    {
        $this->authService->login($request);

        LoggedIn::dispatch($this->authService->teacher());

        return response()->noContent();
    }

    public function logout(Request $request): Response
    {
        $teacher = $this->authService->teacher();

        $this->authService->logout($request);

        LoggedOut::dispatch($teacher);

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
        $status = $this->authService->resetPassword($request);

        return response()->json(['status' => __($status)]);
    }

    public function me()
    {
        $teacher = $this->authService->teacher();

        if ($teacher instanceof Teacher) {
            return response()->json([
                'user' => $teacher,
                'type' => 'teacher',
            ]);
        }

        abort(401);
    }
}
