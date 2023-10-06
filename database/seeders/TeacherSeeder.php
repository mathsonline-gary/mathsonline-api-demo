<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Users\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $traditionalSchools = School::traditionalSchools()->get();

        // Seed static admin teacher.
        Teacher::factory()
            ->admin()
            ->ofSchool($traditionalSchools->first())
            ->create(['username' => 'admin.teacher']);

        // Seed static non-admin teacher.
        Teacher::factory()
            ->nonAdmin()
            ->ofSchool($traditionalSchools->first())
            ->create(['username' => 'non.admin.teacher']);

        // Seed teachers for each traditional school.
        $traditionalSchools->each(function ($school) {
            Teacher::factory()
                ->admin()
                ->ofSchool($school)
                ->create();

            Teacher::factory()
                ->count(13)
                ->nonAdmin()
                ->ofSchool($school)
                ->create();
        });
    }
}
