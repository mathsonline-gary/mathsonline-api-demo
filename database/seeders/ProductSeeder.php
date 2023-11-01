<?php

namespace Database\Seeders;

use App\Enums\SchoolType;
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
                'school_type' => SchoolType::TRADITIONAL_SCHOOL->value,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - 12 Months',
                'stripe_product_id' => 'prod_NZSWkf0NVmmMfe',
                'school_type' => SchoolType::HOMESCHOOL->value,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - 6 Months',
                'stripe_product_id' => 'prod_NZSViVJiClUEl1',
                'school_type' => SchoolType::HOMESCHOOL->value,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - Monthly',
                'stripe_product_id' => 'prod_NZSOAxgHeFZI9W',
                'school_type' => SchoolType::HOMESCHOOL->value,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - 12 Months',
                'stripe_product_id' => 'prod_OuyX7tgVDngCnL',
                'school_type' => SchoolType::HOMESCHOOL->value,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - 6 Months',
                'stripe_product_id' => 'prod_OuyYE2H97wSyRa',
                'school_type' => SchoolType::HOMESCHOOL->value,
            ],
            [
                'market_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - Monthly',
                'stripe_product_id' => 'prod_OuyZ3kraQCIXGf',
                'school_type' => SchoolType::HOMESCHOOL->value,
            ],
        ];

        $products = array_merge($products1);

        DB::table('products')->insert($products);
    }
}
