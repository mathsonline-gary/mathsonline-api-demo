<?php

namespace App\Models\Users;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tutor extends User
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'school_id',
        'email',
        'first_name',
        'last_name',
        'password',
        'is_account_holder',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * Get the tutor's school.
     *
     * @return BelongsTo
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
