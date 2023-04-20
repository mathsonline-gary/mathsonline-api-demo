<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\School;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', School::class);

        return School::all();
    }

    public function store(Request $request)
    {
        $this->authorize('create', School::class);

        $request->validate([
            'market_id' => ['required', 'integer'],
            'name' => ['required'],
            'type' => ['required'],
            'email' => ['nullable', 'email', 'max:254'],
            'phone' => ['nullable'],
            'fax' => ['nullable'],
            'address_line_1' => ['nullable'],
            'address_line_2' => ['nullable'],
            'address_city' => ['nullable'],
            'address_state' => ['nullable'],
            'address_postal_code' => ['nullable'],
            'address_country' => ['nullable'],
        ]);

        return School::create($request->validated());
    }

    public function show(School $school)
    {
        $this->authorize('view', $school);

        return $school;
    }

    public function update(Request $request, School $school)
    {
        $this->authorize('update', $school);

        $request->validate([
            'market_id' => ['required', 'integer'],
            'name' => ['required'],
            'type' => ['required'],
            'email' => ['nullable', 'email', 'max:254'],
            'phone' => ['nullable'],
            'fax' => ['nullable'],
            'address_line_1' => ['nullable'],
            'address_line_2' => ['nullable'],
            'address_city' => ['nullable'],
            'address_state' => ['nullable'],
            'address_postal_code' => ['nullable'],
            'address_country' => ['nullable'],
        ]);

        $school->update($request->validated());

        return $school;
    }

    public function destroy(School $school)
    {
        $this->authorize('delete', $school);

        $school->delete();

        return response()->json();
    }
}
