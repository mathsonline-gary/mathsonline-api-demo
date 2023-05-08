<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'email',
        'title',
        'position',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
