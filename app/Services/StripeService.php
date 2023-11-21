<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\School;
use Illuminate\Support\Arr;
use Stripe\ApiResource;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Subscription;

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
     * @param $attributes
     *
     * @return Customer
     *
     * @throws ApiErrorException
     */
    public function createCustomer($attributes): Customer
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
     * @param School $school
     * @param Membership $membership
     *
     * @return Subscription
     *
     * @throws ApiErrorException
     */
    public function createSubscription(School $school, Membership $membership): Subscription
    {
        $stripe = $this->stripe($school->market_id);

        // Set the parameters for the Stripe subscription.
        $params = [
            'customer' => $school->stripe_id,
            'items' => [
                [
                    'price' => $membership->stripe_id,
                    'quantity' => 1,
                ],
            ],
        ];

        // Set the cancel time if the membership is not recurring.
        if (!$membership->is_recurring) {
            $params['cancel_at_period_end'] = true;
        }

        // Add "test_clock" parameter if the app is running in the "local" or "development" environment.
        if (app()->environment('local', 'development')) {
            $testClock = config("services.stripe.$school->market_id.test_clock");

            if (!empty($testClock)) {
                $params['test_clock'] = $testClock;
            }
        }

        return $stripe->subscriptions->create($params);
    }

    /**
     * Set the default payment method for the given school.
     *
     * @param School $school
     * @param string $paymentToken
     *
     * @return Customer
     *
     * @throws ApiErrorException
     */
    public function setDefaultPaymentMethod(School $school, string $paymentToken): Customer
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
     * @param int $marketId
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
