<?php

namespace App\Models;

use App\Enums\SchoolType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,
        SoftDeletes;

    public $timestamps = false;

    protected $casts = [
        'market_id' => 'integer',
        'school_type' => SchoolType::class,
    ];

    /**
     * Get the memberships for the product.
     *
     * @return HasMany
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
}
