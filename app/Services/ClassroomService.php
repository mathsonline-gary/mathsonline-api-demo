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
     *     with_groups?: bool,
     * } $options
     * @return Collection|LengthAwarePaginator
     */
    public function search(array $options = []): Collection|LengthAwarePaginator
    {
        $searchKey = $options['key'] ?? null;

        $query = Classroom::when($options['with_school'] ?? false, function (Builder $query) {
            $query->with('school');
        })->when($options['with_owner'] ?? true, function (Builder $query) {
            $query->with('owner');
        })->when($options['with_secondary_teachers'] ?? true, function (Builder $query) {
            $query->with('secondaryTeachers');
        })->when($options['with_groups'] ?? false, function (Builder $query) {
            $query->with('classroomGroups');
        })->when(isset($options['school_id']), function (Builder $query) use ($options) {
            $query->where('school_id', $options['school_id']);
        })->when($searchKey && $searchKey !== '', function (Builder $query) use ($searchKey) {
            $query->where('name', 'like', "%$searchKey%");
        })->when(isset($options['owner_id']), function (Builder $query) use ($options) {
            $query->where('owner_id', $options['owner_id']);
        });

        return $query->when($options['pagination'] ?? true, function (Builder $query) {
            return $query->paginate();
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
     *     with_groups?: bool,
     * } $options
     * @return Classroom|null
     */
    public function find(int|string $id, array $options = []): ?Classroom
    {
        $classroom = $options['throwable'] ?? true
            ? Classroom::findOrFail($id)
            : Classroom::find($id);

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

        if ($options['with_groups'] ?? true) {
            $classroom->load('classroomGroups');
        }

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
     *     gorups: array,
     * } $attributes
     * @return Classroom
     */
    public function create(array $attributes): Classroom
    {
        $attributes = Arr::only($attributes, [
            'school_id',
            'owner_id',
            'type',
            'name',
            'pass_grade',
            'attempts',
            'groups'
        ]);

        return DB::transaction(function () use ($attributes) {
            // Create the classroom.
            $classroom = Classroom::create($attributes);

            // Create the default classroom group.
            $this->addDefaultGroup($classroom);

            // Create custom groups if existed.
            if (isset($attributes['groups']) && count($attributes['groups']) > 0) {
                foreach ($attributes['groups'] as $group) {
                    $this->addCustomGroup($classroom, $group);
                }
            }

            return $classroom;
        });
    }

    /**
     * Add the default group for the given classroom, if there is no default group of this classroom.
     *
     * @param Classroom $classroom
     * @return ClassroomGroup|null
     * @throws DefaultClassroomGroupExistsException
     */
    public function addDefaultGroup(Classroom $classroom): ?ClassroomGroup
    {
        if ($classroom->defaultClassroomGroup()->exists()) {
            throw new DefaultClassroomGroupExistsException();
        }

        return $classroom->defaultClassroomGroup()->create([
            'name' => $classroom->name . ' default group',
            'pass_grade' => $classroom->pass_grade,
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
        if ($classroom->classroomGroups()->count() >= Classroom::MAX_GROUP_COUNT) {

            throw new MaxClassroomGroupCountReachedException();
        }

        $attributes = Arr::only($attributes, [
            'name',
            'pass_grade',
        ]);

        return $classroom->customClassroomGroups()->create([
            'name' => $attributes['name'],
            'pass_grade' => $attributes['pass_grade'],
            'is_default' => false,
        ]);
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
}
