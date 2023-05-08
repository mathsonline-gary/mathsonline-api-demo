<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tutor extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
