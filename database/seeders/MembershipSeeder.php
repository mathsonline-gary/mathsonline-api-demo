<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // MathsOnline (AU) memberships.
        $memberships = [
            // Single Memberships.
            [
                'product_id' => 2,
                'campaign_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - 12 Months - DEFAULT',
                'description' => '',
                'price' => 197.00,
                'period_in_months' => 12,
                'period_in_days' => null,
                'is_recurring' => false,
                'user_limit' => 1,
                'stripe_id' => 'price_1MoJbKA8m0obgGbqVMxcjzuN',
                'note' => null,
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
            [
                'product_id' => 2,
                'campaign_id' => 2,
                'name' => 'MathsOnline (AU) Single Membership - 12 Months - HOMESCHOOL',
                'description' => '',
                'price' => 98.50,
                'period_in_months' => 12,
                'period_in_days' => null,
                'is_recurring' => false,
                'user_limit' => 1,
                'stripe_id' => 'price_1MoJqVA8m0obgGbqMLgHCz2o',
                'note' => null,
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
            [
                'product_id' => 3,
                'campaign_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - 6 Months - DEFAULT',
                'description' => '',
                'price' => 127.00,
                'period_in_months' => 6,
                'period_in_days' => null,
                'is_recurring' => false,
                'user_limit' => 1,
                'stripe_id' => 'price_1MoJaeA8m0obgGbqUhFdm2yk',
                'note' => null,
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
            [
                'product_id' => 3,
                'campaign_id' => 2,
                'name' => 'MathsOnline (AU) Single Membership - 6 Months - HOMESCHOOL',
                'description' => '',
                'price' => 63.50,
                'period_in_months' => 6,
                'period_in_days' => null,
                'is_recurring' => false,
                'user_limit' => 1,
                'stripe_id' => 'price_1MoJlvA8m0obgGbqDwjLuKUD',
                'note' => null,
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
            [
                'product_id' => 4,
                'campaign_id' => 1,
                'name' => 'MathsOnline (AU) Single Membership - Monthly - DEFAULT',
                'description' => '',
                'price' => 29.97,
                'period_in_months' => 1,
                'period_in_days' => null,
                'is_recurring' => true,
                'user_limit' => 1,
                'stripe_id' => 'price_1O78dvA8m0obgGbq1TuWrNHI',
                'note' => null,
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
            [
                'product_id' => 4,
                'campaign_id' => 2,
                'name' => 'MathsOnline (AU) Single Membership - Monthly - HOMESCHOOL',
                'description' => '',
                'price' => 13.47,
                'period_in_months' => 1,
                'period_in_days' => null,
                'is_recurring' => true,
                'user_limit' => 1,
                'stripe_id' => 'price_1MoJlIA8m0obgGbql7LJCDbN',
                'note' => null,
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],

            // Family Memberships.
            [
                'product_id' => 5,
                'campaign_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - 12 Months - DEFAULT',
                'description' => '',
                'price' => 297.00,
                'period_in_months' => 12,
                'period_in_days' => null,
                'is_recurring' => false,
                'user_limit' => 3,
                'stripe_id' => 'price_1O78ZzA8m0obgGbqdxYYWoda',
                'note' => null,
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
            [
                'product_id' => 6,
                'campaign_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - 6 Months - DEFAULT',
                'description' => '',
                'price' => 197.00,
                'period_in_months' => 6,
                'period_in_days' => null,
                'is_recurring' => false,
                'user_limit' => 3,
                'stripe_id' => 'price_1O78bSA8m0obgGbqfUoIPeCJ',
                'note' => null,
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
            [
                'product_id' => 7,
                'campaign_id' => 1,
                'name' => 'MathsOnline (AU) Family Membership - Monthly - DEFAULT',
                'description' => '',
                'price' => 29.97,
                'period_in_months' => 1,
                'period_in_days' => null,
                'is_recurring' => true,
                'user_limit' => 3,
                'stripe_id' => 'price_1O78cEA8m0obgGbqT3RfrCjs',
                'note' => null,
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
        ];

        DB::table('memberships')->insert($memberships);
    }
}
