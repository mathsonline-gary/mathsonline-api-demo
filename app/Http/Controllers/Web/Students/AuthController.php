<?php

namespace App\Http\Controllers\Web\Students;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Http\Controllers\Web\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Users\Student;
use App\Services\ActionService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService   $authService,
        protected ActionService $actionService,
    )
    {
    }

    public function login(LoginRequest $request): Response
    {
        $this->authService->login($request);

        LoggedIn::dispatch($this->authService->student());

        return response()->noContent();
    }

    public function logout(Request $request): Response
    {
        $student = $this->authService->student();

        $this->authService->logout($request);

        LoggedOut::dispatch($student);

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

    public function me()
    {
        $student = $this->authService->student();

        if ($student instanceof Student) {
            return response()->json([
                'user' => $student,
                'type' => 'student',
            ]);
        }

        abort(401);
    }
}
