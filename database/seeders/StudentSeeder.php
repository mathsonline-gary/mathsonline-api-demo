<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Users\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed students for each traditional school.
        School::traditionalSchools()->each(function ($school) {
            Student::factory()
                ->count(10)
                ->ofSchool($school)
                ->create();
        });
    }
}
