<?php

namespace Database\Seeders;

use App\Models\Tutor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class TutorSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tutor::factory()
            ->create([
                'market_id' => 1,
                'type_id' => 1,
                'username' => null,
                'email' => 'tutor1@test.com',
                'first_name' => 'Tutor',
                'last_name' => 'One',
            ]);

        Tutor::factory()
            ->count(50)
            ->sequence(
                [
                    'type_id' => 1,
                    'username' => null,
                ],
                [
                    'type_id' => 2,
                    'username' => null,
                ],
                [
                    'type_id' => 3,
                    'email' => null,
                ]
            )
            ->create();
    }
}
