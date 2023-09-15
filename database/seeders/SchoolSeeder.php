<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\Member;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        // Seed homeschools.
        School::factory()
            ->count(10)
            ->homeschool()
            ->has(
                Member::factory()
                    ->state(function (array $attributes, School $school) {
                        return [
                            'email' => $school->email,
                        ];
                    })
            )
            ->has(
                Student::factory()
                    ->count(2)
            )
            ->create();

        // Seed traditional schools.
        School::factory()
            ->traditionalSchool()
            ->has(
                Teacher::factory()
                    ->admin()
                    ->state(['username' => 'teacher.admin',])
            )
            ->has(
                Teacher::factory()
                    ->state(['username' => 'non.admin.teacher'])
            )
            ->has(
                Teacher::factory()
                    ->count(13)
            )
            ->has(
                Student::factory()
                    ->state(['username' => 'example.student'])
            )
            ->has(
                Student::factory()
                    ->count(30)
            )
            ->create();

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
