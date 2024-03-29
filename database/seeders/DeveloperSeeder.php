<?php

namespace Database\Seeders;

use App\Models\Users\Developer;
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
                'email' => 'developer1@test.com',
                'first_name' => 'Developer',
                'last_name' => 'One',
            ]);

        Developer::factory()
            ->count(10)
            ->create();
    }
}
