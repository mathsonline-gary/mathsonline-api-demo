<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Models\Subscription;
use App\Models\Users\Member;
use App\Services\MembershipService;
use App\Services\StripeService;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct(
        protected MembershipService   $membershipService,
        protected SubscriptionService $subscriptionService,
        protected StripeService       $stripeService,
    )
    {
    }

    public function store(StoreSubscriptionRequest $request)
    {
        $this->authorize('create', Subscription::class);

        $authenticatedUser = $request->user();

        if ($authenticatedUser->isMember()) {
            /** @var Member $authenticatedMember */
            $authenticatedMember = $authenticatedUser->asMember();

            // Authorize if the member can subscribe to the membership.
            {
                $membershipId = $request->integer('membership_id');

                $membership = $this->membershipService->find($membershipId, [
                    'throwable' => true,
                    'with_product' => true,
                    'with_campaign' => true,
                ]);

                if (!$authenticatedMember->school->canSubscribeToMembership($membership)) {
                    return $this->errorResponse(
                        message: 'The member is unauthorized to subscribe to the membership.',
                        status: 403,
                    );
                }
            }

            // Create a subscription for the member.
            $subscription = DB::transaction(function () use ($authenticatedMember, $membership) {
                // Create a Stripe subscription for the member.
                $stripeSubscription = $this->stripeService->createSubscription($authenticatedMember->school, $membership);

                // Create a subscription for the member.
                return $this->subscriptionService->create([
                    'school_id' => $authenticatedMember->school->id,
                    'membership_id' => $membership->id,
                    'stripe_id' => $stripeSubscription->id,
                    'starts_at' => $stripeSubscription->start_date,
                    'cancels_at' => $stripeSubscription->cancel_at,
                    'current_period_starts_at' => $stripeSubscription->current_period_start,
                    'current_period_ends_at' => $stripeSubscription->current_period_end,
                    'canceled_at' => $stripeSubscription->canceled_at,
                    'ended_at' => $stripeSubscription->ended_at,
                    'status' => $stripeSubscription->status,
                ]);
            });

            return $this->successResponse(
                data: [
                    'subscription' => $subscription,
                ],
                message: 'Subscribed to the membership successfully.',
                status: 201,
            );
        }

        return $this->errorResponse(
            message: 'Failed to subscribe to the membership.',
        );
    }
}
