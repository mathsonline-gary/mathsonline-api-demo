<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Teacher extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'school_id',
        'username',
        'email',
        'first_name',
        'last_name',
        'title',
        'position',
    ];

    protected $hidden = [
        'password',
    ];


    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
