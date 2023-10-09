<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        // Seed sample traditional school.
        School::factory()
            ->traditionalSchool()
            ->create([
                'name' => 'Sample Traditional School',
                'email' => 'sample@school.com',
                'market_id' => 1,
            ]);

        // Seed sample homeschool.
        School::factory()
            ->homeschool()
            ->create([
                'name' => 'Sample Homeschool',
                'email' => 'sample@homeschool.com',
                'market_id' => 1,
            ]);

        // Seed traditional schools.
        School::factory()
            ->count(10)
            ->traditionalSchool()
            ->create();

        // Seed homeschools.
        School::factory()
            ->count(10)
            ->homeschool()
            ->create();
    }
}
