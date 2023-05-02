<?php

namespace App\Services;

use App\Models\Users\Tutor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TutorService
{
    /**
     * Create a new tutor.
     *
     * @param array $attributes
     * @return Tutor
     */
    public function create(array $attributes): Tutor
    {
        $attributes = Arr::only($attributes, [
            'market_id',
            'school_id',
            'type_id',
            'username',
            'email',
            'first_name',
            'last_name',
            'password',
        ]);

        $attributes['password'] = Hash::make($attributes['password']);

        $tutor = Tutor::create($attributes);

        Log::info('Tutor created: ', $tutor->only([
            'id',
            'username',
            'email',
            'first_name',
            'last_name',
        ]));

        return $tutor;
    }
}
