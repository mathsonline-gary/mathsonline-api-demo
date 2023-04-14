<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginTeacherRequest;
use Illuminate\Http\Request;

class TeacherAuthController extends Controller
{
    public function login(LoginTeacherRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }
}
