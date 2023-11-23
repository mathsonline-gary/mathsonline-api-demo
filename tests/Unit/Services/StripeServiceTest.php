<?php

namespace Tests\Unit\Services;

use App\Models\Membership;
use App\Models\Product;
use App\Services\StripeService;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;
use Tests\TestCase;

/**
 * @see StripeService
 */
class StripeServiceTest extends TestCase
{
    protected StripeService $stripeService;

    protected StripeClient $stripe;

    protected int $marketId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripeService = new StripeService();

        $this->stripe = $this->newStripeClient($this->marketId);
    }

    public function test_it_creates_the_customer(): void
    {
        $payload = [
            'market_id' => $this->marketId,
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'email' => fake()->email,
            'phone' => fake()->phoneNumber,
            'address_line_1' => fake()->streetAddress,
            'address_line_2' => fake()->streetAddress,
            'address_city' => fake()->city,
            'address_state' => fake()->city,
            'address_postal_code' => fake()->postcode,
            'address_country' => fake()->countryCode,
        ];

        $customer = $this->stripeService->createCustomer($payload);

        // Assert that the customer was created.
        $this->assertEquals($payload['email'], $customer->email);
        $this->assertEquals($payload['first_name'] . ' ' . $payload['last_name'], $customer->name);
        $this->assertEquals($payload['phone'], $customer->phone);
        $this->assertEquals($payload['address_line_1'], $customer->address->line1);
        $this->assertEquals($payload['address_line_2'], $customer->address->line2);
        $this->assertEquals($payload['address_city'], $customer->address->city);
        $this->assertEquals($payload['address_state'], $customer->address->state);
        $this->assertEquals($payload['address_postal_code'], $customer->address->postal_code);
        $this->assertEquals($payload['address_country'], $customer->address->country);
        $this->assertEquals($payload['first_name'] . ' ' . $payload['last_name'], $customer->shipping->name);
        $this->assertEquals($payload['phone'], $customer->shipping->phone);
        $this->assertEquals($payload['address_line_1'], $customer->shipping->address->line1);
        $this->assertEquals($payload['address_line_2'], $customer->shipping->address->line2);
        $this->assertEquals($payload['address_city'], $customer->shipping->address->city);
        $this->assertEquals($payload['address_state'], $customer->shipping->address->state);
        $this->assertEquals($payload['address_postal_code'], $customer->shipping->address->postal_code);
        $this->assertEquals($payload['address_country'], $customer->shipping->address->country);
    }

    public function test_it_creates_the_monthly_subscription(): void
    {
        $member = $this->fakeMember($this->marketId);

        $membership = Membership::whereIn('product_id', Product::where('market_id', $this->marketId)->pluck('id'))
            ->where('iterations', null)
            ->firstOrFail();

        $subscription = $this->stripeService->createSubscription($member->school, $membership);

        // Assert that the subscription was created correctly.
        $this->assertInstanceOf(StripeSubscription::class, $subscription);
        $this->assertEquals($member->school->stripe_id, $subscription->customer);
        $this->assertEquals($membership->stripe_id, $subscription->items->data[0]->price->id);
        $this->assertEquals('active', $subscription->status);
        $this->assertNull($subscription->cancel_at);
    }

    public function test_it_creates_the_one_time_subscription(): void
    {
        $member = $this->fakeMember($this->marketId);

        $membership = Membership::whereIn('product_id', Product::where('market_id', $this->marketId)->pluck('id'))
            ->where('iterations', 1)
            ->firstOrFail();

        $subscription = $this->stripeService->createSubscription($member->school, $membership);

        // Assert that the subscription was created correctly.
        $this->assertInstanceOf(StripeSubscription::class, $subscription);
        $this->assertEquals($member->school->stripe_id, $subscription->customer);
        $this->assertEquals($membership->stripe_id, $subscription->items->data[0]->price->id);
        $this->assertEquals('active', $subscription->status);
        $this->assertNotNull($subscription->cancel_at);
    }
}
