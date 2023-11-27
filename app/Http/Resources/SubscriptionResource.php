<?php

namespace App\Http\Resources;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subscription
 */
class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'membership_id' => $this->membership_id,
            'school_id' => $this->school_id,
            'stripe_id' => $this->stripe_id,
            'starts_at' => $this->starts_at,
            'cancels_at' => $this->cancels_at,
            'current_period_starts_at' => $this->current_period_starts_at,
            'current_period_ends_at' => $this->current_period_ends_at,
            'canceled_at' => $this->canceled_at,
            'ended_at' => $this->ended_at,
            'status' => $this->status,
            'custom_user_limit' => $this->custom_user_limit,
            'membership' => $this->whenLoaded('membership'),
        ];
    }
}
