<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Tutor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed homeschools.
        School::factory()
            ->has(
                Tutor::factory()
                    ->count(1)
                    ->state(function (array $attributes, School $school) {
                        return [
                            'market_id' => $school->market_id,
                            'type_id' => 1,
                            'email' => $school->email,
                        ];
                    })
            )
            ->has(
                Tutor::factory()
                    ->count(1)
                    ->state(function (array $attributes, School $school) {
                        return [
                            'market_id' => $school->market_id,
                            'type_id' => 2,
                        ];
                    })
            )
            ->count(10)
            ->state([
                'type' => 'homeschool',
            ])
            ->create();

        // Seed traditional_schools.
        School::factory()
            ->has(
                Teacher::factory()
                    ->count(15)
                    ->state(function (array $attributes, School $school) {
                        return [
                            'market_id' => $school->market_id,
                        ];
                    })
            )
            ->has(
                Student::factory()
                    ->count(30)
                    ->state(function (array $attributes, School $school) {
                        return [
                            'market_id' => $school->market_id,
                        ];
                    })
            )
            ->count(10)
            ->state([
                'type' => 'traditional_school',
            ])
            ->create();
    }
}
