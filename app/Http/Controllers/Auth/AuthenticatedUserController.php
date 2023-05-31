<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedUserController extends Controller
{
    public function show(Request $request)
    {
        $userType = config('sanctum.guard');

        return response()->json([
            'user' => $request->user(),
            'type' => $userType,
        ]);
    }
}
