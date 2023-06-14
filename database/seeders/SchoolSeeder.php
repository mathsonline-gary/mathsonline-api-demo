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
            ->count(10)
            ->homeschool()
            ->has(
                Tutor::factory()
                    ->primary()
                    ->state(function (array $attributes, School $school) {
                        return [
                            'email' => $school->email,
                        ];
                    })
            )
            ->has(
                Tutor::factory()
                    ->secondary()
            )
            ->has(
                Student::factory()
                    ->count(2)
            )
            ->create();

        // Seed traditional schools.
        School::factory()
            ->count(10)
            ->traditionalSchool()
            ->has(
                Teacher::factory()
                    ->admin()
            )
            ->has(
                Teacher::factory()
                    ->count(14)
            )
            ->has(
                Student::factory()
                    ->count(30)
            )
            ->create();
    }
}
