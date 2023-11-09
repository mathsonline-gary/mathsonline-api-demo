<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    /**
     * Find a product by given Stripe product ID.
     *
     * @param string $stripeId
     * @return Product|null
     */
    public function findByStripeId(string $stripeId): ?Product
    {
        return Product::where('stripe_id', $stripeId)->first();
    }
}
