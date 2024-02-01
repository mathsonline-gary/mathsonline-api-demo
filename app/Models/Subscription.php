<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stripe\Subscription as StripeSubscription;

class Subscription extends Model
{
    use HasFactory;

    // Define subscription status constants.
    public const STATUS_ACTIVE = StripeSubscription::STATUS_ACTIVE;
    public const STATUS_INCOMPLETE = StripeSubscription::STATUS_INCOMPLETE;
    public const STATUS_INCOMPLETE_EXPIRED = StripeSubscription::STATUS_INCOMPLETE_EXPIRED;
    public const STATUS_PAST_DUE = StripeSubscription::STATUS_PAST_DUE;
    public const STATUS_CANCELED = StripeSubscription::STATUS_CANCELED;
    public const STATUS_UNPAID = StripeSubscription::STATUS_UNPAID;
    public const STATUS_TRIALING = StripeSubscription::STATUS_TRIALING;
    public const STATUS_PAUSED = StripeSubscription::STATUS_PAUSED;
    public const STATUS_UNKNOWN = 'unknown';

    // Define payment method constants.
    public const PAYMENT_METHOD_CARD = 'card';
    public const PAYMENT_METHOD_DIRECT_DEPOSIT = 'direct_deposit';

    protected $fillable = [
        'membership_id',
        'starts_at',
        'cancels_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'canceled_at',
        'ended_at',
        'status',
        'custom_user_limit',
    ];

    protected $casts = [
        'school_id' => 'int',
        'membership_id' => 'int',
        'starts_at' => 'datetime',
        'cancels_at' => 'datetime',
        'current_period_starts_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => 'string',
        'custom_user_limit' => 'int',
    ];

    /**
     * Scope a query to only include active subscriptions.
     *
     * @param Builder $query
     *
     * @return void
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Determine if the subscription has been canceled.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Get the associated membership.
     *
     * @return BelongsTo
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Get the associated school.
     *
     * @return BelongsTo
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
