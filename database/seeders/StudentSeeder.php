<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Student::factory()
            ->create([
                'market_id' => 1,
                'username' => 'student1',
                'email' => 'student1@test.com',
                'first_name' => 'Student',
                'last_name' => 'One',
            ]);

        Student::factory()
            ->count(200)
            ->create();
    }
}
