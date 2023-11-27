<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\Users\Member;
use App\Models\Users\Teacher;
use App\Services\MembershipService;
use App\Services\StripeService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Subscription::class);

        $authenticatedUser = $request->user();

        // Get the subscriptions for members.
        if ($authenticatedUser->isMember()) {
            /** @var Member $authenticatedMember */
            $authenticatedMember = $authenticatedUser->asMember();

            $payload = [
                'school_id' => $authenticatedMember->school->id,
                'pagination' => false,
                'with_membership' => true,
            ];

            // Get the subscriptions.
            $subscriptions = $this->subscriptionService->search($payload);

            return $this->successResponse(SubscriptionResource::collection($subscriptions));
        }

        // Get the subscriptions for admin teachers.
        if ($authenticatedUser->isTeacher()) {
            /** @var Teacher $authorizedTeacher */
            $authorizedTeacher = $authenticatedUser->asTeacher();

            $payload = [
                'school_id' => $authorizedTeacher->school->id,
                'pagination' => false,
                'with_membership' => true,
            ];

            // Get the subscriptions.
            $subscriptions = $this->subscriptionService->search($payload);

            return $this->successResponse(SubscriptionResource::collection($subscriptions));
        }

        return $this->errorResponse(
            message: 'Failed to retrieve subscriptions.',
        );
    }

    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $this->authorize('create', Subscription::class);

        $authenticatedUser = $request->user();

        if ($authenticatedUser->isMember()) {
            /** @var Member $authenticatedMember */
            $authenticatedMember = $authenticatedUser->asMember();

            // Validate the membership.
            $membership = $request->validateMembership($authenticatedMember->school);

            // Create a Stripe subscription for the member.
            DB::transaction(function () use ($authenticatedMember, $membership, $request) {
                // Set the default payment method for the member.
                $this->stripeService->setDefaultPaymentMethod(
                    $authenticatedMember->school,
                    $request->string('payment_token_id'),
                );

                // Create a Stripe subscription for the member.
                $this->stripeService->createSubscription($authenticatedMember->school, $membership);

                // DO NOT CREATE A SUBSCRIPTION IN THE DATABASE HERE.
                // THE SUBSCRIPTION WILL BE CREATED IN THE WEBHOOK.
            });

            return $this->successResponse(
                message: 'Subscribed to the membership successfully.',
                status: 201,
            );
        }

        return $this->errorResponse(
            message: 'Failed to subscribe to the membership.',
        );
    }
}
