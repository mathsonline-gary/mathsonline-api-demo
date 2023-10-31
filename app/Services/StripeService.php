<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeService
{
    /**
     * Connect to the Stripe account.
     *
     * @param int $marketId
     * @return StripeClient
     */
    public function stripe(int $marketId): StripeClient
    {
        $secretKey = config("services.stripe.{$marketId}.secret");

        return new StripeClient($secretKey);
    }

    /**
     * Create a new Stripe customer.
     * @param $attributes
     * @return Customer
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
            'payment_source',
        ]);

        $stripe = $this->stripe($attributes['market_id']);

        return $stripe->customers->create([
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
//            'source' => $attributes['payment_source'],
        ]);
    }
}
