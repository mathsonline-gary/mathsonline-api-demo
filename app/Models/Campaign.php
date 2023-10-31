<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Indicate if the campaign is active.
     * The campaign is active if it has no expiration date or the expiration date is in the future.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return is_null($this->expires_at) || $this->expires_at->isFuture();
    }
}
