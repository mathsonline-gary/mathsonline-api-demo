<?php

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\Membership;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    /**
     * @see Membership::product()
     */
    public function test_it_belongs_to_a_product(): void
    {
        $membership = Membership::inRandomOrder()->first();
        $associatedProduct = Product::find($membership->product_id);

        // Assert that it has a BelongsTo relationship with the product.
        $this->assertInstanceOf(BelongsTo::class, $membership->product());

        // Assert that the membership has a relationship with the product.
        $this->assertInstanceOf(Product::class, $membership->product);
        $this->assertEquals($associatedProduct->id, $membership->product->id);
    }

    /**
     * @see Membership::campaign()
     */
    public function test_it_belongs_to_a_campaign(): void
    {
        $membership = Membership::inRandomOrder()->first();
        $associatedCampaign = Campaign::find($membership->campaign_id);

        // Assert that it has a BelongsTo relationship with the campaign.
        $this->assertInstanceOf(BelongsTo::class, $membership->campaign());

        // Assert that the membership has a relationship with the campaign.
        $this->assertInstanceOf(Campaign::class, $membership->campaign);
        $this->assertEquals($associatedCampaign->id, $membership->campaign->id);
    }
}
