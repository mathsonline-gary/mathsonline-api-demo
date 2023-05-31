<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed traditional classrooms.
        $traditionalSchools = School::traditionalSchools()->get();

        $traditionalSchools->each(function ($school) {
            $teachers = $school->teachers;

            // Seed classrooms.
            $classrooms = Classroom::factory()
                ->ofSchool($school)
                ->ownedBy($teachers->random())
                ->count(5)
                ->create();

            // Seed secondary teachers for each classroom.
            $classrooms->each(function ($classroom) use ($teachers) {
                /* @var $classroom Classroom */
                $classroom->secondaryTeachers()->attach($teachers->random(3));
            });
        });
    }
}
