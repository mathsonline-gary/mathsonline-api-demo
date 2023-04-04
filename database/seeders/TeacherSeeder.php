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
            ->create([
                'market_id' => 1,
                'username' => 'teacher1',
                'email' => 'teacher1@test.com',
                'first_name' => 'Teacher',
                'last_name' => 'One',
            ]);

        Teacher::factory()
            ->count(50)
            ->create();
    }
}
