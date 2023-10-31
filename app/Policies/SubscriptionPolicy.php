<?php

namespace App\Policies;

use App\Models\Users\User;

class SubscriptionPolicy
{
    public function create(User $user): bool
    {
        if ($user->isMember()) {
            return true;
        }

        return false;
    }
}
