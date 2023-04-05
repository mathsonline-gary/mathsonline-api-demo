<?php

namespace Database\Seeders;

use App\Models\Developer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeveloperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Developer::factory()
            ->create([
                'username' => 'developer1',
                'email' => 'developer1@test.com',
                'first_name' => 'Developer',
                'last_name' => 'One',
            ]);

        Developer::factory()
            ->count(10)
            ->create();
    }
}
