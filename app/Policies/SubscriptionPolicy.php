<?php

namespace App\Policies;

use App\Models\Users\User;

class SubscriptionPolicy
{
    /**
     * Determine whether the user can create a subscription.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        if ($user->isMember()) {
            return !is_null($user->asMember()->school->stripe_id);
        }

        return false;
    }
}
