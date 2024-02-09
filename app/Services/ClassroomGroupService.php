<?php

namespace App\Services;

use App\Exceptions\DeleteDefaultClassroomGroupException;
use App\Exceptions\MaxClassroomGroupCountReachedException;
use App\Models\Classroom;
use App\Models\ClassroomGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClassroomGroupService
{
    /**
     * Add a custom group for the given classroom, if it has not hit the max count.
     *
     * @param Classroom $classroom
     * @param array     $attributes
     *
     * @return ClassroomGroup|null
     * @throws MaxClassroomGroupCountReachedException
     */
    public function createCustom(Classroom $classroom, array $attributes): ?ClassroomGroup
    {
        if ($classroom->customClassroomGroups()->count() >= Classroom::MAX_CUSTOM_GROUP_COUNT) {

            throw new MaxClassroomGroupCountReachedException();
        }

        $attributes = Arr::only($attributes, [
            'name',
            'pass_grade',
            'attempts',
        ]);

        return $classroom->customClassroomGroups()->create([
            'name' => $attributes['name'],
            'pass_grade' => $attributes['pass_grade'],
            'attempts' => $attributes['attempts'],
            'is_default' => false,
        ]);
    }

    /**
     * Update the classroom group with given valid attributes.
     *
     * @param ClassroomGroup $group
     * @param array          $attributes
     *
     * @return ClassroomGroup
     */
    public function update(ClassroomGroup $group, array $attributes): ClassroomGroup
    {
        $group->update(Arr::only($attributes, [
            'name',
            'pass_grade',
            'attempts',
        ]));

        return $group;
    }

    /**
     * Delete the given custom classroom group, detach its students.
     *
     * @param ClassroomGroup $group
     *
     * @return void
     * @throws DeleteDefaultClassroomGroupException|Throwable
     */
    public function deleteCustom(ClassroomGroup $group): void
    {
        if ($group->isDefault()) {
            throw new DeleteDefaultClassroomGroupException();
        }

        DB::transaction(function () use ($group) {
            // Move students in this group to the default group.
            $group->classroom->defaultClassroomGroup->students()->syncWithoutDetaching($group->students);

            // Detach students from this group.
            $group->students()->detach();

            // Delete classroom group.
            $group->delete();
        });
    }

    /**
     * Assign students to the given classroom group.
     *
     * @param ClassroomGroup $classroomGroup
     * @param array          $studentIds
     *
     * @return void
     */
    public function assignStudents(ClassroomGroup $classroomGroup, array $studentIds): void
    {
        $classroomGroup->students()->syncWithoutDetaching($studentIds);
    }
}
