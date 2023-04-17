<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginStudentRequest;
use App\Http\Requests\Auth\LoginTutorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TutorAuthController extends Controller
{
    public function login(LoginTutorRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }

    public function logout(Request $request)
    {
        Auth::guard('tutor')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
