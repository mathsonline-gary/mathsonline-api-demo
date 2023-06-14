<?php

namespace App\Http\Controllers\Web\Developers;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Http\Controllers\Web\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Users\Developer;
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

        LoggedIn::dispatch($this->authService->developer());

        return response()->noContent();
    }

    public function logout(Request $request): Response
    {
        $developer = $this->authService->developer();

        $this->authService->logout($request);

        LoggedOut::dispatch($developer);

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
        $developer = $request->user();

        if ($developer instanceof Developer) {
            return response()->json([
                'user' => $developer,
                'type' => 'developer',
            ]);
        }

        abort(401);
    }
}
