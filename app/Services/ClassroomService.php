<?php

namespace App\Services;

use App\Exceptions\DefaultClassroomGroupExistsException;
use App\Exceptions\MaxClassroomGroupCountReachedException;
use App\Models\Classroom;
use App\Models\ClassroomGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ClassroomService
{
    /**
     * Search classrooms by options.
     *
     * @param array{
     *     school_id?: int,
     *     key?: string,
     *     owner_id?: int|string,
     *     pagination?: bool,
     *     with_school?: bool,
     *     with_owner?: bool,
     *     with_secondary_teachers?: bool,
     *     with_custom_groups?: bool,
     * } $options
     * @return Collection|LengthAwarePaginator
     */
    public function search(array $options = []): Collection|LengthAwarePaginator
    {
        $searchKey = $options['key'] ?? null;

        return Classroom::with('defaultClassroomGroup')
            ->when($options['with_school'] ?? false, function (Builder $query) {
                $query->with('school');
            })
            ->when($options['with_owner'] ?? true, function (Builder $query) {
                $query->with('owner');
            })
            ->when($options['with_secondary_teachers'] ?? true, function (Builder $query) {
                $query->with('secondaryTeachers');
            })
            ->when($options['with_custom_groups'] ?? false, function (Builder $query) {
                $query->with('customClassroomGroups');
            })
            ->when(isset($options['school_id']), function (Builder $query) use ($options) {
                $query->where('school_id', $options['school_id']);
            })
            ->when($searchKey && $searchKey !== '', function (Builder $query) use ($searchKey) {
                $query->where('name', 'like', "%$searchKey%");
            })
            ->when(isset($options['owner_id']), function (Builder $query) use ($options) {
                $query->where('owner_id', $options['owner_id']);
            })
            ->when($options['pagination'] ?? true, function (Builder $query) {
                return $query->paginate()->withQueryString();
            }, function (Builder $query) {
                return $query->get();
            });
    }

    /**
     * Find a classroom record by ID with additional options.
     *
     * @param int|string $id
     * @param array{
     *     throwable?: bool,
     *     with_school?: bool,
     *     with_owner?: bool,
     *     with_secondary_teachers?: bool,
     *     with_custom_groups?: bool,
     * } $options
     * @return Classroom|null
     */
    public function find(int|string $id, array $options = []): ?Classroom
    {
        $classroom = Classroom::when($options['throwable'] ?? true, function (Builder $query) use ($id) {
            return $query->findOrFail($id);
        }, function (Builder $query) use ($id) {
            return $query->find($id);
        });

        if ($options['with_school'] ?? true) {
            $classroom->load('school');
        }

        if ($options['with_owner'] ?? true) {
            $classroom->load('owner');
        }

        if ($options['with_secondary_teachers'] ?? true) {
            $classroom->load(['secondaryTeachers' => function (BelongsToMany $query) use ($classroom) {
                $query->where('school_id', $classroom->school_id);
            }]);
        }

        if ($options['with_custom_groups'] ?? true) {
            $classroom->load('customClassroomGroups');
        }

        $classroom->load('defaultClassroomGroup');

        return $classroom;
    }

    /**
     * Create a classroom by given attributes.
     *
     * @param array{
     *     school_id: string|int,
     *     owner_id: string|int,
     *     type: string,
     *     name: string,
     *     pass_grade: int,
     *     attempts: int,
     *     mastery_enabled: bool,
     *     self_rating_enabled: bool,
     *     gorups: array,
     * } $attributes
     *
     * @return Classroom
     */
    public function create(array $attributes): Classroom
    {
        $attributes = Arr::only($attributes, [
            'school_id',
            'owner_id',
            'year_id',
            'type',
            'name',
            'pass_grade',
            'attempts',
            'secondary_teacher_ids',
            'mastery_enabled',
            'self_rating_enabled',
            'groups',
        ]);

        return DB::transaction(function () use ($attributes) {
            // Create the classroom.
            $classroom = Classroom::create($attributes);

            // Create the default classroom group.
            $this->addDefaultGroup($classroom, [
                'pass_grade' => $attributes['pass_grade'],
                'attempts' => $attributes['attempts'],
            ]);

            return $classroom;
        });
    }

    /**
     * Add the default group for the given classroom, if there is no default group of this classroom.
     *
     * @param Classroom $classroom
     * @param array{
     *     pass_grade: int,
     *     attempts: int,
     * } $attributes
     * @return ClassroomGroup|null
     * @throws DefaultClassroomGroupExistsException
     */
    public function addDefaultGroup(Classroom $classroom, array $attributes): ?ClassroomGroup
    {
        if ($classroom->defaultClassroomGroup()->exists()) {
            throw new DefaultClassroomGroupExistsException();
        }

        $attributes = Arr::only($attributes, [
            'pass_grade',
            'attempts',
        ]);

        return $classroom->defaultClassroomGroup()->create([
            'name' => $classroom->name . ' default group',
            'pass_grade' => $attributes['pass_grade'],
            'attempts' => $attributes['attempts'],
            'is_default' => true,
        ]);
    }

    /**
     * Add a custom group for the given classroom, if it has not hit the max count.
     *
     * @param Classroom $classroom
     * @param array $attributes
     * @return ClassroomGroup|null
     * @throws MaxClassroomGroupCountReachedException
     */
    public function addCustomGroup(Classroom $classroom, array $attributes): ?ClassroomGroup
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
     * Add secondary teachers to the given classroom.
     *
     * @param Classroom $classroom The classroom to add secondary teachers to.
     * @param array $teacherIds The IDs of the teachers to add.
     * @param bool $detaching Whether to detach the existing secondary teachers or not.
     * @return void
     */
    public function assignSecondaryTeachers(Classroom $classroom, array $teacherIds, bool $detaching = true): void
    {
        $detaching
            ? $classroom->secondaryTeachers()->sync($teacherIds)
            : $classroom->secondaryTeachers()->syncWithoutDetaching($teacherIds);
    }

    /**
     * Remove secondary teachers from the given classroom.
     *
     * @param Classroom $classroom The classroom to remove secondary teachers from.
     * @param array $teacherIds The IDs of the teachers to remove.
     * @return void
     */
    public function removeSecondaryTeachers(Classroom $classroom, array $teacherIds): void
    {
        $classroom->secondaryTeachers()->detach($teacherIds);
    }

    /**
     * Update a classroom with given valid attributes.
     *
     * @param Classroom $classroom
     * @param array $attributes
     * @return Classroom
     */
    public function update(Classroom $classroom, array $attributes): Classroom
    {
        DB::transaction(function () use ($classroom, $attributes) {
            // Update classroom.
            $classroom->update(Arr::only($attributes, [
                'name',
                'owner_id',
            ]));

            // Update default group.
            $classroom->defaultClassroomGroup()->update(Arr::only($attributes, [
                'pass_grade',
                'attempts',
            ]));
        });

        return $classroom;
    }

    /**
     * Update the given classroom group with given valid attributes.
     *
     * @param ClassroomGroup $group
     * @param array $attributes
     * @return ClassroomGroup
     */
    public function updateGroup(ClassroomGroup $group, array $attributes): ClassroomGroup
    {
        DB::transaction(function () use ($group, $attributes) {
            $group->update(Arr::only($attributes, [
                'name',
                'pass_grade',
                'attempts',
            ]));
        });

        return $group;
    }

    /**
     * Delete the given classroom, its groups and its pivot data (secondary teachers, students).
     *
     * @param Classroom $classroom
     * @return void
     */
    public function delete(Classroom $classroom): void
    {
        DB::transaction(function () use ($classroom) {
            // Detach all secondary teachers.
            $classroom->secondaryTeachers()->detach();

            // Detach all students.
            $classroom->classroomGroups()
                ->each(function (ClassroomGroup $group) {
                    $group->students()->detach();
                });

            // Delete classroom groups.
            $classroom->classroomGroups()->delete();

            // Delete classroom.
            $classroom->delete();
        });
    }

    /**
     * Delete the given classroom group, detach its students.
     *
     * @param ClassroomGroup $group
     * @return void
     */
    public function deleteGroup(ClassroomGroup $group): void
    {
        DB::transaction(function () use ($group) {
            // Detach all students.
            $group->students()->detach();

            // Delete classroom group.
            $group->delete();
        });
    }
}
