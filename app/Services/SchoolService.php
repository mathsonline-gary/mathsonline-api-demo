<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class SchoolService
{
    /**
     * Create a new school.
     *
     * @param array $attributes
     * @return School
     */
    public function create(array $attributes)
    {
        $attributes = Arr::only($attributes, [
            'market_id',
            'name',
            'type',
            'email',
            'phone',
            'fax',
            'address_line_1',
            'address_line_2',
            'address_city',
            'address_state',
            'address_postal_code',
            'address_country',
        ]);

        $school = School::create($attributes);

        Log::info('New school created: ', $attributes);

        return $school;
    }
}
