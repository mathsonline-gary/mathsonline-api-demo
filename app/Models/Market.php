<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    public $timestamps = false;

    /**
     * Get the years associated with the market.
     *
     * @return HasMany
     */
    public function years(): HasMany
    {
        return $this->hasMany(Year::class);
    }
}
