<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Web\Controller;
use Illuminate\Http\Request;

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
