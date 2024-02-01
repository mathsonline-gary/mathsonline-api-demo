<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    public const MOL_AU = 1;
    public const CM_UK = 2;
    public const MB_NZ = 3;
    public const MOL_US = 4;
    public const MOL_KE = 5;
    public const DEV_AU = 6;
    public const MB_SA = 7;
    public const CTC_US = 8;
    public const MOL_IN = 9;

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
