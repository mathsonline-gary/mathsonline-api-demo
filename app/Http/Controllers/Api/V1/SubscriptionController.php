<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Models\Subscription;
use App\Services\MembershipService;

class SubscriptionController extends Controller
{
    public function __construct(
        protected MembershipService $membershipService,
    )
    {
    }

    public function store(StoreSubscriptionRequest $request)
    {
        $this->authorize('create', Subscription::class);

        $authenticatedUser = $request->user();

        if ($authenticatedUser->isMember()) {
            $authenticatedMember = $authenticatedUser->asMember();

            // Validate the membership.
            {
                $membershipId = $request->integer('membership_id');

                $membership = $this->membershipService->find($membershipId, [
                    'throwable' => true,
                    'with_product' => true,
                    'with_campaign' => true,
                ]);

                if (!$membership->isSubscribableByMember($authenticatedMember)) {
                    return $this->errorResponse(
                        message: 'The membership is not subscribable by the authenticated member.',
                        status: 422,
                    );
                }
            }
        }

        return $this->errorResponse(
            message: 'Failed to subscribe to the membership.',
        );
    }
}
