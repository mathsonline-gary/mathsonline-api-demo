<?php

namespace App\Http\Controllers\Auth;

use App\Events\Auth\LoggedIn;
use App\Http\Controllers\Controller;
use App\Models\Users\User;
use App\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;

class GoogleOAuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    )
    {
    }

    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handle(): Response|JsonResponse
    {
        $googleUser = Socialite::driver('google')->user();

        if (!$googleUser) {
            // If the is empty or invalid, return an error.
            return response()->json([
                'message' => 'Invalid Google account.',
            ], 422);
        }

        $googleId = $googleUser->getId();

        $user = User::where('oauth_google_id', $googleId)->first();

        if (!$user) {
            // If the linked user does not exist, return an error with the Google ID.
            return response()->json([
                'message' => 'No user linked to the Google account.',
                'oauth_google_id' => $googleId,
            ], 401);

        }

        // If the linked user exists, log in.
        auth()->login($user);
        LoggedIn::dispatch($user, Carbon::now());

        return response()->noContent();
    }
}
