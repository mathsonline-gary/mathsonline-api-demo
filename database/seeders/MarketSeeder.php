<?php

namespace Database\Seeders;

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
            [
                "id" => 1,
                "name" => "Australia",
                "country" => "Australia",
                "country_code" => "AU",
                "timezone" => "Australia/Sydney",
                "product" => "MathsOnline",
                "website" => "https://dev-www.mathsonline.com.au/",
                "domain" => "dev-www.mathsonline.com.au",
                "marketing_domain" => "MathsOnline.com.au",
                "info_email" => "info@mathsonline.com.au",
                "feedback_email" => "feedback@mathsonline.com.au",
                "no_reply_email" => "noreply@mathsonline.com.au"
            ],
            [
                "id" => 2,
                "name" => "United Kingdom",
                "country" => "United Kingdom",
                "country_code" => "UK",
                "timezone" => "Europe/London",
                "product" => "ConquerMaths",
                "website" => "https://www.conquermaths.com/",
                "domain" => "www.conquermaths.com",
                "marketing_domain" => "ConquerMaths.com",
                "info_email" => "info@conquermaths.com",
                "feedback_email" => "feedback@conquermaths.com",
                "no_reply_email" => "noreply@conquermaths.com"
            ],
            [
                "id" => 3,
                "name" => "New Zealand",
                "country" => "New Zealand",
                "country_code" => "NZ",
                "timezone" => "Pacific/Auckland",
                "product" => "MathsBuddy",
                "website" => "https://dev-www.mathsonline.com.au/sites/mathsbuddy/",
                "domain" => "www.mathsbuddy.co.nz",
                "marketing_domain" => "MathsBuddy.co.nz",
                "info_email" => "info@mathsbuddy.co.nz",
                "feedback_email" => "feedback@mathsbuddy.co.nz",
                "no_reply_email" => "noreply@mathsbuddy.co.nz"
            ],
            [
                "id" => 4,
                "name" => "United States",
                "country" => "United States",
                "country_code" => "US",
                "timezone" => "America/Chicago",
                "product" => "MathOnline",
                "website" => "https://www.mathonline.com/",
                "domain" => "www.mathonline.com",
                "marketing_domain" => "MathOnlineUS.com",
                "info_email" => "info@mathonline.com",
                "feedback_email" => "feedback@mathonline.com",
                "no_reply_email" => "noreply@mathonline.com"
            ],
            [
                "id" => 5,
                "name" => "Kenya",
                "country" => "Kenya",
                "country_code" => "KE",
                "timezone" => "Africa/Nairobi",
                "product" => "MathsOnline",
                "website" => "https://www.mathsonline.co.ke/",
                "domain" => "www.mathsonline.co.ke",
                "marketing_domain" => "MathsOnline.co.ke",
                "info_email" => "info@mathsonline.co.ke",
                "feedback_email" => "feedback@mathsonline.co.ke",
                "no_reply_email" => "noreply@mathsonline.co.ke"
            ],
            [
                "id" => 6,
                "name" => "Australia",
                "country" => "Australia",
                "country_code" => "AU",
                "timezone" => "Australia/Sydney",
                "product" => "Development",
                "website" => "https://www.mathsonline.com.au/",
                "domain" => "www.mathsonline.com.au",
                "marketing_domain" => "MathsOnline.com.au",
                "info_email" => "info@mathsonline.com.au",
                "feedback_email" => "feedback@mathsonline.com.au",
                "no_reply_email" => "noreply@mathsonline.com.au"
            ],
            [
                "id" => 7,
                "name" => "South Africa",
                "country" => "South Africa",
                "country_code" => "SA",
                "timezone" => "Africa/Johannesburg",
                "product" => "MathsBuddy",
                "website" => "https://www.mathsbuddy.co.za/",
                "domain" => "www.mathsbuddy.co.za",
                "marketing_domain" => "MathsBuddy.co.za",
                "info_email" => "info@mathsbuddy.co.za",
                "feedback_email" => "feedback@mathsbuddy.co.za",
                "no_reply_email" => "noreply@mathsbuddy.co.za"
            ],
            [
                "id" => 8,
                "name" => "United States",
                "country" => "United States",
                "country_code" => "US",
                "timezone" => "America/Chicago",
                "product" => "CTCMath",
                "website" => "https://dev-www.mathsonline.com.au/sites/ctcmath/",
                "domain" => "www.ctcmath.com",
                "marketing_domain" => "CTCMath.com",
                "info_email" => "info@ctcmath.com",
                "feedback_email" => "feedback@ctcmath.com",
                "no_reply_email" => "noreply@ctcmath.com"
            ],
            [
                "id" => 9,
                "name" => "India",
                "country" => "India",
                "country_code" => "IN",
                "timezone" => "Asia/Kolkata",
                "product" => "MathsOnline",
                "website" => "https://www.mathsonline.co.in/",
                "domain" => "www.mathsonline.co.in",
                "marketing_domain" => "MathsOnline.co.in",
                "info_email" => "info@mathsonline.co.in",
                "feedback_email" => "feedback@mathsonline.co.in",
                "no_reply_email" => "noreply@mathsonline.co.in"
            ]
        ];

        DB::table('markets')
            ->insert($seed);
    }
}
