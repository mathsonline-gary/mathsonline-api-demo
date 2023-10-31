<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Arr;

class SchoolService
{
    /**
     * Create a new school.
     *
     * @param array $attributes
     * @return School
     */
    public function create(array $attributes): School
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
            'stripe_customer_id',
        ]);

        return School::create($attributes);
    }
}
