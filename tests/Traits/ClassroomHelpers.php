<?php

namespace Tests\Traits;

use App\Models\Classroom;
use App\Models\Users\Teacher;
use Ramsey\Collection\Collection;

trait ClassroomHelpers
{
    /**
     * Create classroom(s) for the given teacher.
     *
     * @param Teacher $owner
     * @param int $count
     * @param array $attributes
     * @return Classroom|Collection<Classroom>
     */
    public function createClassroom(Teacher $owner, int $count = 1, array $attributes = []): Collection|Classroom
    {
        $classrooms = Classroom::factory()
            ->count($count)
            ->ofSchool($owner->school)
            ->ownedBy($owner)
            ->create($attributes);

        return $count === 1 ? $classrooms->first() : $classrooms;
    }


    /**
     * Add secondary teacher(s) for the given classroom.
     *
     * @param Classroom $classroom
     * @param array<int> $teacherIds
     * @return void
     */
    public function addSecondaryTeachers(Classroom $classroom, array $teacherIds): void
    {
        $classroom->secondaryTeachers()
            ->attach($teacherIds);
    }
}
