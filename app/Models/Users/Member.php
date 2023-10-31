<?php

namespace App\Models\Users;

use App\Concerns\BelongsToUser;
use App\Models\School;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use HasFactory,
        BelongsToUser;

    protected $fillable = [
        'user_id',
        'school_id',
        'email',
        'first_name',
        'last_name',
    ];

    protected $casts = [
        'user_id' => 'int',
        'school_id' => 'int',
    ];

    /**
     * Get the member's school.
     *
     * @return BelongsTo
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the member's market ID.
     *
     * @return Attribute
     */
    public function marketId(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->school->market_id,
        );
    }
}
