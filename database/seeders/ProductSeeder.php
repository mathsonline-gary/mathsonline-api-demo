<?php

namespace Database\Seeders;

use App\Models\School;
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
                'stripe_id' => 'prod_OyMq7aTUXN3uyd',
                'school_type' => School::TYPE_TRADITIONAL_SCHOOL,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - 12 Months',
                'stripe_id' => 'prod_NZSWkf0NVmmMfe',
                'school_type' => School::TYPE_HOMESCHOOL,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - 6 Months',
                'stripe_id' => 'prod_NZSViVJiClUEl1',
                'school_type' => School::TYPE_HOMESCHOOL,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - Monthly',
                'stripe_id' => 'prod_NZSOAxgHeFZI9W',
                'school_type' => School::TYPE_HOMESCHOOL,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - 12 Months',
                'stripe_id' => 'prod_OuyX7tgVDngCnL',
                'school_type' => School::TYPE_HOMESCHOOL,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - 6 Months',
                'stripe_id' => 'prod_OuyYE2H97wSyRa',
                'school_type' => School::TYPE_HOMESCHOOL,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - Monthly',
                'stripe_id' => 'prod_OuyZ3kraQCIXGf',
                'school_type' => School::TYPE_HOMESCHOOL,
            ],
        ];

        $products = array_merge($products1);

        DB::table('products')->insert($products);
    }
}
