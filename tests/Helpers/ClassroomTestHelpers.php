<?php

namespace Tests\Helpers;

use App\Models\Classroom;
use App\Models\ClassroomGroup;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

trait ClassroomTestHelpers
{
    /**
     * Create fake classroom(s) for the given teacher, and add default classroom group(s) of each.
     *
     * @param Teacher $owner
     * @param int     $count
     * @param array   $attributes
     *
     * @return Classroom|Collection<Classroom>
     */
    public function fakeClassroom(Teacher $owner, int $count = 1, array $attributes = []): Collection|Classroom
    {
        $classrooms = Classroom::factory()
            ->count($count)
            ->ofSchool($owner->school)
            ->ownedBy($owner)
            ->has(ClassroomGroup::factory()
                ->default()
                ->state(function () use ($attributes) {
                    return Arr::only($attributes, ['pass_grade', 'attempts']);
                })
            )
            ->create(Arr::only($attributes, ['type', 'name']));

        return $count === 1 ? $classrooms->first() : $classrooms;
    }
    
    /**
     * Add secondary teacher(s) for the given classroom, with detaching all existing secondary teachers.
     *
     * @param Classroom  $classroom
     * @param array<int> $teacherIds
     *
     * @return void
     */
    public function attachSecondaryTeachersToClassroom(Classroom $classroom, array $teacherIds): void
    {
        // TODO: remove this.
        $classroom->secondaryTeachers()
            ->attach($teacherIds);
    }

    /**
     * Add custom classroom groups to the given classroom.
     *
     * @param Classroom $classroom
     * @param int       $count
     * @param array     $attributes
     *
     * @return Collection|ClassroomGroup
     */
    public function fakeCustomClassroomGroup(Classroom $classroom, int $count = 1, array $attributes = []): Collection|ClassroomGroup
    {
        // TODO: move to ClassroomGroupTestHelpers.
        $groups = ClassroomGroup::factory()
            ->count($count)
            ->ofClassroom($classroom)
            ->custom()
            ->create($attributes);

        return $count === 1 ? $groups->first() : $groups;
    }

    /**
     * Add student(s) to the given classroom group.
     *
     * @param ClassroomGroup $classroomGroup
     * @param array          $studentIds
     *
     * @return void
     */
    public function attachStudentsToClassroomGroup(ClassroomGroup $classroomGroup, array $studentIds): void
    {
        // TODO: remove this.
        $classroomGroup->students()
            ->attach($studentIds);
    }

}
