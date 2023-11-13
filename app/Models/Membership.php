<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'memberships';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_recurring' => 'bool',
        'period_in_days' => 'int',
        'period_in_months' => 'int',
        'price' => 'double',
        'user_limit' => 'int',
        'stripe_id' => 'int',
    ];

    /**
     * Get the product of the membership.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the campaign of the membership.
     *
     * @return BelongsTo
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Determine if the membership is recurring.
     *
     * @return bool
     */
    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

}
