<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\School;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Stripe\ApiResource;
use Stripe\Customer as StripeCustomer;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;

class StripeService
{
    /**
     * Connect to the Stripe account.
     *
     * @param int $marketId
     *
     * @return StripeClient
     */
    public function stripe(int $marketId): StripeClient
    {
        $secretKey = config("services.stripe.$marketId.secret");

        return new StripeClient($secretKey);
    }

    /**
     * Create a new Stripe customer.
     *
     * @param $attributes
     *
     * @return StripeCustomer
     *
     * @throws ApiErrorException
     */
    public function createCustomer($attributes): StripeCustomer
    {
        $attributes = Arr::only($attributes, [
            'market_id',
            'email',
            'first_name',
            'last_name',
            'phone',
            'address_line_1',
            'address_line_2',
            'address_city',
            'address_state',
            'address_postal_code',
            'address_country',
        ]);

        $stripe = $this->stripe($attributes['market_id']);

        $params = [
            'email' => $attributes['email'],
            'name' => "{$attributes['first_name']} {$attributes['last_name']}",
            'phone' => $attributes['phone'],
            'address' => [
                'line1' => $attributes['address_line_1'],
                'line2' => $attributes['address_line_2'],
                'city' => $attributes['address_city'],
                'state' => $attributes['address_state'],
                'postal_code' => $attributes['address_postal_code'],
                'country' => $attributes['address_country'],
            ],
            'shipping' => [
                'address' => [
                    'line1' => $attributes['address_line_1'],
                    'line2' => $attributes['address_line_2'],
                    'city' => $attributes['address_city'],
                    'state' => $attributes['address_state'],
                    'postal_code' => $attributes['address_postal_code'],
                    'country' => $attributes['address_country'],
                ],
                'name' => "{$attributes['first_name']} {$attributes['last_name']}",
                'phone' => $attributes['phone'],
            ],
        ];

        // Add "test_clock" parameter if the app is running in the "local" or "development" environment.
        if (app()->environment('local', 'development')) {
            $testClock = config("services.stripe.{$attributes['market_id']}.test_clock");

            if (!empty($testClock)) {
                $params['test_clock'] = $testClock;
            }
        }

        return $stripe->customers->create($params);
    }

    /**
     * Create a new Stripe subscription for the given school with the given membership.
     *
     * @param School     $school
     * @param Membership $membership
     *
     * @return StripeSubscription
     *
     * @throws ApiErrorException
     */
    public function createSubscription(School $school, Membership $membership): StripeSubscription
    {
        $stripe = $this->stripe($school->market_id);

        // Create the Stripe subscription conditionally.
        if ($membership->isRecurring()) {
            // If the membership is recurring, create a subscription.
            $subscription = $stripe->subscriptions->create([
                'customer' => $school->stripe_id,
                'enable_incomplete_payments' => "false",
                'off_session' => "true",
                'items' => [
                    [
                        'price' => $membership->stripe_id,
                        'quantity' => "1",
                    ],
                ],
            ]);
        } else {
            // Otherwise, create a subscription schedule.
            $subscriptionSchedule = $stripe->subscriptionSchedules->create([
                'customer' => $school->stripe_id,
                'end_behavior' => 'cancel',
                'start_date' => 'now',
                'phases' => [
                    [
                        'items' => [
                            [
                                'price' => $membership->stripe_id,
                                'quantity' => 1,
                            ],
                        ],
                        'iterations' => $membership->iterations,
                    ],
                ],
                'expand' => ['subscription'],
            ]);

            $subscription = $subscriptionSchedule->subscription;
        }

        return $subscription;
    }

    /**
     * Set the default payment method for the given school.
     *
     * @param School $school
     * @param string $paymentToken
     *
     * @return StripeCustomer
     *
     * @throws ApiErrorException
     */
    public function setDefaultPaymentMethod(School $school, string $paymentToken): StripeCustomer
    {
        $stripe = $this->stripe($school->market_id);

        return $stripe->customers->update(
            $school->stripe_id,
            [
                'source' => $paymentToken,
            ]);
    }

    /**
     * Get the refreshed Stripe resource.
     *
     * @param ApiResource $resource
     * @param int         $marketId
     *
     * @return ApiResource
     *
     * @throws ApiErrorException
     */
    public function refreshResource(ApiResource $resource, int $marketId): ApiResource
    {
        Stripe::setApiKey(config("services.stripe.$marketId.secret"));

        return $resource->refresh();
    }

    /**
     * Parse the given Stripe subscription to subscription at.
     *
     * @param StripeSubscription $stripeSubscription
     *
     * @return array{
     *     starts_at: Carbon,
     *     cancels_at: Carbon|null,
     *     current_period_starts_at: Carbon,
     *     current_period_ends_at: Carbon,
     *     canceled_at: Carbon|null,
     *     ended_at: Carbon|null,
     *     status: string,
     *     membership: Membership|null,
     *     school: School|null,
     *     subscription: Subscription|null,
     * }
     */
    public function parseSubscriptionAttributes(StripeSubscription $stripeSubscription): array
    {
        // Get the price ID from the first item to find the linked membership.
        $priceId = $stripeSubscription->items->first()->price->id;

        $attributes = [
            'stripe_id' => $stripeSubscription->id,
            'starts_at' => new Carbon($stripeSubscription->start_date),
            'cancels_at' => $stripeSubscription->cancel_at ? new Carbon($stripeSubscription->cancel_at) : null,
            'current_period_starts_at' => new Carbon($stripeSubscription->current_period_start),
            'current_period_ends_at' => new Carbon($stripeSubscription->current_period_end),
            'canceled_at' => $stripeSubscription->canceled_at ? new Carbon($stripeSubscription->canceled_at) : null,
            'ended_at' => $stripeSubscription->ended_at ? new Carbon($stripeSubscription->ended_at) : null,
            'status' => $stripeSubscription->status,
            'membership' => null,
            'membership_id' => null,
            'school' => null,
            'school_id' => null,
            'subscription' => null,
        ];

        // Get the linked membership if it exists.
        if ($membership = Membership::where('stripe_id', $priceId)->first()) {
            $attributes['membership'] = $membership;
            $attributes['membership_id'] = $membership->id;
        }

        // Get the linked school if it exists.
        if ($school = School::where('stripe_id', $stripeSubscription->customer)->first()) {
            $attributes['school'] = $school;
            $attributes['school_id'] = $school->id;
        }

        // Get the linked subscription if it exists.
        if ($attributes['school']) {
            $attributes['subscription'] = $school->subscriptions()
                ->where('stripe_id', $stripeSubscription->id)
                ->first();
        }

        return $attributes;
    }
}
