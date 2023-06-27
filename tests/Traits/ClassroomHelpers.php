<?php

namespace Tests\Traits;

use App\Models\Classroom;
use App\Models\ClassroomGroup;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Collection;

trait ClassroomHelpers
{
    /**
     * Create classroom(s) for the given teacher, and add default classroom group(s) of each.
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
            ->has(ClassroomGroup::factory()->default())
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

    /**
     * Add custom classroom groups to the given classroom.
     *
     * @param Classroom $classroom
     * @param int $count
     * @param array $attributes
     * @return Collection|ClassroomGroup
     */
    public function createCustomClassroomGroup(Classroom $classroom, int $count = 1, array $attributes = []): Collection|ClassroomGroup
    {
        $groups = ClassroomGroup::factory()
            ->count($count)
            ->ofClassroom($classroom)
            ->custom()
            ->create($attributes);

        return $count === 1 ? $groups->first() : $groups;
    }
}
