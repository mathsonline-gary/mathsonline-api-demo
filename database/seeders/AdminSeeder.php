<?php

namespace Database\Seeders;

use App\Models\Users\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::factory()
            ->create([
                'market_id' => 1,
                'username' => 'admin1',
                'email' => 'admin1@test.com',
                'first_name' => 'Admin',
                'last_name' => 'One',
            ]);

        Admin::factory()
            ->count(20)
            ->create();
    }
}
