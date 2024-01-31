<?php

namespace App\Http\Controllers\Auth;

use App\Events\Auth\LoggedIn;
use App\Events\Auth\LoggedOut;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Users\User;
use App\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    )
    {
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $type = $user->type;

        $profile = match ($type) {
            User::TYPE_STUDENT => $user->asStudent(),
            User::TYPE_TEACHER => new TeacherResource($user->asTeacher()),
            User::TYPE_MEMBER => $user->asMember(),
            User::TYPE_ADMIN => $user->asAdmin(),
            User::TYPE_DEVELOPER => $user->asDeveloper(),
            default => null,
        };

        return response()->json([
            'data' => [
                'user_type' => $type,
                'profile' => $profile,
            ],
        ]);
    }

    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        LoggedIn::dispatch($this->authService->user(), Carbon::now());

        return response()->noContent();
    }

    public function destroy(Request $request): Response
    {
        $user = $this->authService->user();

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        LoggedOut::dispatch($user, Carbon::now());

        return response()->noContent();
    }
}
