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
        // Seed homeschools.
        School::factory()
            ->homeschool()
            ->has(
                Tutor::factory()
                    ->count(1)
                    ->state(function (array $attributes, School $school) {
                        return [
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
                            'type_id' => 2,
                        ];
                    })
            )
            ->has(
                Student::factory()
                    ->count(2)
            )
            ->count(10)
            ->create();

        // Seed traditional schools.
        School::factory()
            ->traditionalSchool()
            ->has(
                Teacher::factory()
                    ->count(15)
            )
            ->has(
                Student::factory()
                    ->count(30)
            )
            ->count(10)
            ->create();
    }
}
