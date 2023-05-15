<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\Tutor;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed tutor-created schools.
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
            ->has(
                Student::factory()
                    ->count(2)
                    ->state(function (array $attributes, School $school) {
                        return [
                            'market_id' => $school->market_id,
                        ];
                    })
            )
            ->count(10)
            ->state([
                'type' => 'homeschool',
            ])
            ->create();

        // Seed traditional schools.
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
                'type' => 'traditional school',
            ])
            ->create();
    }
}
