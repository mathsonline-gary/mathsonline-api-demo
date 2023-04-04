<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Teacher::factory()
            ->count(50)
            ->sequence(
                ['position' => 'Year 1 Teacher'],
                ['position' => 'Year 2 Teacher'],
                ['position' => 'Year 3 Teacher'],
                ['position' => 'Year 4 Teacher'],
                ['position' => 'Year 5 Teacher'],
            )
            ->create();
    }
}
