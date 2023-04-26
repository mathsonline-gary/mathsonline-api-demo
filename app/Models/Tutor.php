<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tutor extends User
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'school_id',
        'type_id',
        'username',
        'email',
        'first_name',
        'last_name',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
