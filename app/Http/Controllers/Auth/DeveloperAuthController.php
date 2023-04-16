<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginDeveloperRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeveloperAuthController extends Controller
{
    public function login(LoginDeveloperRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }

    public function logout(Request $request)
    {
        Auth::guard('developer')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
