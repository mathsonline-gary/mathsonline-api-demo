<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            MarketSeeder::class,
            SchoolSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            AdminSeeder::class,
            DeveloperSeeder::class,
            YearSeeder::class,
            ClassroomSeeder::class,
            ProductSeeder::class,
            CampaignSeeder::class,
            MembershipSeeder::class,
        ]);
    }
}
