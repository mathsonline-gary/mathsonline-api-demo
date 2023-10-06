<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
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
