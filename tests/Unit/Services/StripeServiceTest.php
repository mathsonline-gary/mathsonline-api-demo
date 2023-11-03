<?php

namespace Tests\Unit\Services;

use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class StripeServiceTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * @see StripeService::createCustomer()
     */
    public function test_it_creates_the_customer(): void
    {
        // TODO
        $this->assertTrue(true);
    }

    public function test_it_creates_the_subscription(): void
    {
        // TODO
    }
}
