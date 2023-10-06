<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomGroup;
use App\Models\School;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed classrooms for each traditional school.
        School::traditionalSchools()->each(function (School $school) {
            $teachers = $school->teachers;
            $students = $school->students;

            // Seed classrooms.
            $classrooms = Classroom::factory()
                ->count(5)
                ->ofSchool($school)
                ->ownedBy($teachers->random())
                ->create();

            $classrooms->each(function (Classroom $classroom) use ($teachers, $students) {
                // Seed secondary teachers for each classroom.
                $classroom->secondaryTeachers()->attach($teachers->except($classroom->owner->id)->random(2));

                // Seed the default classroom group.
                $defaultClassroomGroup = ClassroomGroup::factory()
                    ->for($classroom)
                    ->default()
                    ->create([
                        'name' => 'Default group for Classroom ' . $classroom->id,
                    ]);

                // Seed custom classroom groups.
                $customClassroomGroups = ClassroomGroup::factory()
                    ->count(2)
                    ->for($classroom)
                    ->custom()
                    ->create();

                // Seed students for each classroom group
                $defaultClassroomGroup->students()->attach($students->random(2));
                $customClassroomGroups->each(function (ClassroomGroup $classroomGroup) use ($students) {
                    $classroomGroup->students()->attach($students->random(5));
                });
            });
        });
    }
}
