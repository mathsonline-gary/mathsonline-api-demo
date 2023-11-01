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
        'stripe_subscription_id',
        'starts_at',
        'cancels_at',
        'canceled_at',
        'ended_at',
        'status',
        'custom_price',
        'custom_user_limit',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'status' => SubscriptionStatus::class,
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
