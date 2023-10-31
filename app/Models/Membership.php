<?php

namespace App\Models;

use App\Models\Users\Member;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{

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
     * Get the market ID of the membership.
     *
     * @return Attribute
     */
    public function marketId(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->product->market_id,
        );
    }

    /**
     * Indicate if the membership is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->campaign->isActive();
    }

    /**
     * Indicate if the membership is subscribable by the given member.
     *
     * @param Member $member
     * @return bool
     */
    public function isSubscribableByMember(Member $member): bool
    {
        // The membership's product must in the same market as the authenticated member.
        if ($this->market_id === $member->market_id &&
            $this->isActive()) {
            return true;
        }

        return false;
    }
}
