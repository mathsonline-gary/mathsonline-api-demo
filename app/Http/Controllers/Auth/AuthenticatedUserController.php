<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedUserController extends Controller
{
    public function show(Request $request)
    {
        $guard = Auth::getDefaultDriver();

        return response()->json([
            'user' => $request->user(),
            'role' => $guard,
        ]);
    }
}
