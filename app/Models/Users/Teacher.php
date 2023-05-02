<?php

namespace App\Models\Users;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teacher extends User
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
