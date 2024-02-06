<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\School;
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
     * @param string     $collectionMethod
     *
     * @return StripeSubscription
     *
     * @throws ApiErrorException
     */
    public function createSubscription(School $school, Membership $membership, string $collectionMethod = StripeSubscription::COLLECTION_METHOD_SEND_INVOICE): StripeSubscription
    {
        $stripe = $this->stripe($school->market_id);

        // Create the Stripe subscription conditionally.
        if ($membership->isRecurring()) {
            // If the membership is recurring, create a subscription.
            $params = [
                'customer' => $school->stripe_id,
                'enable_incomplete_payments' => "false",
                'off_session' => "true",
                'items' => [
                    [
                        'price' => $membership->stripe_id,
                        'quantity' => "1",
                    ],
                ],
            ];

            if ($collectionMethod === StripeSubscription::COLLECTION_METHOD_SEND_INVOICE) {
                $params['collection_method'] = StripeSubscription::COLLECTION_METHOD_SEND_INVOICE;
                $params['days_until_due'] = 7;
            }

            $subscription = $stripe->subscriptions->create($params);
        } else {
            // Otherwise, create a subscription schedule.
            $params = [
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
            ];

            if ($collectionMethod === StripeSubscription::COLLECTION_METHOD_SEND_INVOICE) {
                $params['phases'][0]['collection_method'] = StripeSubscription::COLLECTION_METHOD_SEND_INVOICE;
                $params['phases'][0]['invoice_settings']['days_until_due'] = 7;
            }

            $subscriptionSchedule = $stripe->subscriptionSchedules->create($params);

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

}
