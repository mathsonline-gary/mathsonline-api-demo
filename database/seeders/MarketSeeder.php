<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seed = [
            // MathsOnline (AU)
            [
                'name' => 'MathsOnline (AU)',
                'country' => 'Australia',
                'country_code' => 'AU',
                'timezone' => 'Australia/Sydney',
                'utc_offset' => 10,
                'product' => 'MathsOnline',
                'website' => 'https://www.mathsonline.com.au',
                'domain' => 'mathsonline.com.au',
                'marketing_domain' => 'MathsOnline.com.au',
                'info_email' => 'info@mathsonline.com.au',
                'feedback_email' => 'feedback@mathsonline.com.au',
                'no_reply_email' => 'noreply@mathsonline.com.au',
            ],

            // MathsBuddy (NZ)
            [
                'name' => 'MathsBuddy (NZ)',
                'country' => 'New Zealand',
                'country_code' => 'NZ',
                'timezone' => 'Pacific/Auckland',
                'utc_offset' => 12,
                'product' => 'MathsBuddy',
                'website' => 'https://www.mathsbuddy.co.nz',
                'domain' => 'mathsbuddy.co.nz',
                'marketing_domain' => 'MathsBuddy.co.nz',
                'info_email' => 'info@mathsbuddy.co.nz',
                'feedback_email' => 'feedback@mathsbuddy.co.nz',
                'no_reply_email' => 'noreply@mathsbuddy.co.nz',
            ],
        ];

        DB::table('markets')
            ->insert($seed);
    }
}
