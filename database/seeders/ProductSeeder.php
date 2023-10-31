<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // MathsOnline (AU) products.
        $products1 = [
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) School Membership',
                'stripe_product_id' => '',
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - 12 Months',
                'stripe_product_id' => 'prod_NZSWkf0NVmmMfe',
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - 6 Months',
                'stripe_product_id' => 'prod_NZSViVJiClUEl1',
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - Monthly',
                'stripe_product_id' => 'prod_NZSOAxgHeFZI9W',
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - 12 Months',
                'stripe_product_id' => 'prod_OuyX7tgVDngCnL',
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - 6 Months',
                'stripe_product_id' => 'prod_OuyYE2H97wSyRa',
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - Monthly',
                'stripe_product_id' => 'prod_OuyZ3kraQCIXGf',
            ],
        ];

        $products = array_merge($products1);

        DB::table('products')->insert($products);
    }
}
