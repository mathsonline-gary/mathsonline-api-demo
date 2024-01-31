<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = [
            [
                'code' => 'DEFAULT',
                'description' => 'The Regular Price',
                'expires_at' => null,
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
            [
                'code' => 'HOMESCHOOL',
                'description' => 'Homeschoolers receive 50% off our regular price',
                'expires_at' => '2030-01-01 00:00:00',
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
            [
                'code' => '12PLUS6',
                'description' => 'Get 6 months free when You purchase a 12 month plan',
                'expires_at' => '2030-01-01 00:00:00',
                'created_at' => '2017-01-01 00:00:00',
                'updated_at' => '2017-01-01 00:00:00',
            ],
        ];

        DB::table('campaigns')->insert($campaigns);
    }
}
