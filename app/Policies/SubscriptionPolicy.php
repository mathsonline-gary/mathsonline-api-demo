<?php

namespace App\Policies;

use App\Models\Users\User;
use Illuminate\Auth\Access\Response;

class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isMember()) {
            // The member can only view their own subscriptions. This is enforced in the controller.
            return true;
        }

        if ($user->isTeacher()
            && $user->asTeacher()->isAdmin()) {
            // The admin teacher can only view their own subscriptions. This is enforced in the controller.
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create a subscription.
     *
     * @param User $user
     * @return bool|Response
     */
    public function create(User $user): bool|Response
    {
        if ($user->isMember()) {
            // The member should be our Stripe customer.
            if (is_null($user->asMember()->school->stripe_id)) {
                return Response::deny('The member is not connected to Stripe.');
            }

            // The member should not currently have any active subscription.
            if ($user->asMember()->school->hasActiveSubscription()) {
                return Response::deny('The member already has an active subscription.');
            }

            return true;
        }

        return false;
    }
}
