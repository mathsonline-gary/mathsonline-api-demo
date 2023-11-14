<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'membership_id',
        'stripe_id',
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
        'status' => SubscriptionStatus::class,
        'custom_user_limit' => 'int',
    ];

    /**
     * Scope a query to only include active subscriptions.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', SubscriptionStatus::ACTIVE->value);
    }
}
