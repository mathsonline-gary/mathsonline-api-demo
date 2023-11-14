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

    public function test_it_indicates_whether_it_is_recurring(): void
    {
        // Retrieve a random recurring membership.
        $membership = Membership::where('is_recurring', true)->inRandomOrder()->first();

        // Ensure that the $membership variable is not null
        $this->assertNotNull($membership);

        // Test the isRecurring method for the retrieved membership
        $this->assertTrue($membership->isRecurring());

        // Retrieve a random non-recurring membership.
        $membership = Membership::where('is_recurring', false)->inRandomOrder()->first();

        // Ensure that the $membership variable is not null
        $this->assertNotNull($membership);

        // Test the isRecurring method for the retrieved membership
        $this->assertFalse($membership->isRecurring());
    }
}
