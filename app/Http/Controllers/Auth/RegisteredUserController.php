<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function store(RegisterRequest $request): Response
    {
        $validated = $request->safe()->only([
            'first_name',
            'last_name',
            'email',
            'password',
            'phone',
            'address_line_1',
            'address_line_2',
            'address_city',
            'address_state',
            'address_postcode',
            'address_country'
        ]);
        
        $tutor = Tutor::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'type_id' => 1,
            'market_id' => 1,
            'school_id' => 2,
        ]);

        event(new Registered($tutor));

        Auth::guard($request->guard)->login($tutor);

        return response()->noContent();
    }
}
