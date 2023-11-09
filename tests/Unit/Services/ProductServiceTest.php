<?php

namespace Tests\Unit\Services;

use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var ProductService
     */
    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productService = new ProductService();
    }

    /**
     * @see ProductService::findByStripeId
     */
    public function test_it_finds_product_by_stripe_id(): void
    {
        // TODO
        $this->assertTrue(true);
    }
}
