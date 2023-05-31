<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomGroup;
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
            $students = $school->students;

            // Seed classrooms.
            $classrooms = Classroom::factory()
                ->ofSchool($school)
                ->ownedBy($teachers->random())
                ->count(5)
                ->create();

            $classrooms->each(function ($classroom) use ($teachers, $students) {
                // Seed secondary teachers for each classroom.
                /* @var $classroom Classroom */
                $classroom->secondaryTeachers()->attach($teachers->random(3));

                // Seed the default classroom group.
                $defaultClassroomGroup = ClassroomGroup::factory()
                    ->for($classroom)
                    ->default(true)
                    ->create();

                // Seed custom classroom groups.
                $customClassroomGroups = ClassroomGroup::factory()
                    ->for($classroom)
                    ->default(false)
                    ->count(2)
                    ->create();

                // Seed students for each classroom and classroom group
                $studentsInClass = $students->random(10);

                $defaultClassroomGroup->students()->attach($studentsInClass);

                $customClassroomGroups->each(function ($classroomGroup) use ($studentsInClass) {
                    /* @var $classroomGroup ClassroomGroup */
                    $classroomGroup->students()->attach($studentsInClass->random(5));
                });
            });
        });
    }
}
