<?php

namespace App\Models;

use App\Enums\SchoolType;
use App\Models\Users\Member;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'name',
        'type',
        'email',
        'phone',
        'fax',
        'address_line_1',
        'address_line_2',
        'address_city',
        'address_state',
        'address_postal_code',
        'address_country',
        'stripe_id',
    ];

    protected $casts = [
        'market_id' => 'int',
        'stripe_id' => 'string',
        'type' => SchoolType::class,
    ];

    /**
     * Get the market associated with the school.
     *
     * @return BelongsTo
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Get the owner of the school (homeschool).
     *
     * @return HasOne
     */
    public function owner(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    /**
     * Get the teachers of the school (traditional school).
     *
     * @return HasMany
     */
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    /**
     * Get the school's students.
     *
     * @return HasMany
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get the school's classrooms.
     *
     * @return HasMany
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * Scope a query to only include traditional schools.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeTraditionalSchools(Builder $query): Builder
    {
        return $query->where('type', SchoolType::TRADITIONAL_SCHOOL);
    }

    /**
     * Scope a query to only include homeschools.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeHomeschools(Builder $query): Builder
    {
        return $query->where('type', SchoolType::HOMESCHOOL);
    }

    /**
     * Get the school's subscriptions.
     *
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Indicate if the school has an active subscription.
     *
     * @return bool
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()->active()->exists();
    }

    /**
     * Indicate if the school is permitted to subscribe to the given membership.
     * The school can subscribe to a membership if:
     * 1. The school is in the same market as the membership's product
     * 2. The membership's campaign is active
     * 3. The school has no active subscription
     * 4. The school's type matches the membership's product type
     *
     * @param Membership $membership
     * @return bool
     */
    public function canSubscribeToMembership(Membership $membership): bool
    {
        return $this->market_id === $membership->product->market_id
            && $membership->campaign->isActive()
            && !$this->hasActiveSubscription()
            && $this->type === $membership->product->school_type;
    }

}
