<?php

namespace App\Http\Controllers\Web\Students;

use App\Http\Controllers\Web\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Users\Student;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {
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
        $student = $request->user();

        if ($student instanceof Student) {
            return response()->json([
                'user' => $student,
                'type' => 'student',
            ]);
        }

        abort(401);
    }
}
