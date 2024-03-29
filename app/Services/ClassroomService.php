<?php

namespace App\Services;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClassroomService
{
    /**
     * Search classrooms by options.
     *
     * @param array{
     *     school_id?: int,
     *     search_key?: string,
     *     owner_id?: int|string,
     *     pagination?: bool,
     *     per_page?: int,
     *     with_school?: bool,
     *     with_owner?: bool,
     *     with_secondary_teachers?: bool,
     *     with_groups?: bool,
     * } $options
     *
     * @return Collection|LengthAwarePaginator
     */
    public function search(array $options = []): Collection|LengthAwarePaginator
    {
        $searchKey = $options['search_key'] ?? null;

        return Classroom::with('defaultClassroomGroup')
            ->when($options['with_school'] ?? false, function (Builder $query) {
                $query->with('school');
            })
            ->when($options['with_owner'] ?? false, function (Builder $query) {
                $query->with('owner');
            })
            ->when($options['with_secondary_teachers'] ?? false, function (Builder $query) {
                $query->with('secondaryTeachers');
            })
            ->when($options['with_groups'] ?? false, function (Builder $query) {
                $query->with('classroomGroups');
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
            ->when($options['pagination'] ?? true, function (Builder $query) use ($options) {
                return $query->paginate($options['per_page'] ?? 20)->withQueryString();
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
     * }                 $options
     *
     * @return Classroom|null
     */
    public function find(int|string $id, array $options = []): ?Classroom
    {
        $classroom = Classroom::when($options['throwable'] ?? true, function (Builder $query) use ($id) {
            return $query->findOrFail($id);
        }, function (Builder $query) use ($id) {
            return $query->find($id);
        });

        if ($options['with_school'] ?? false) {
            $classroom->load('school');
        }

        if ($options['with_owner'] ?? false) {
            $classroom->load('owner');
        }

        if ($options['with_secondary_teachers'] ?? false) {
            $classroom->load(['secondaryTeachers' => function (BelongsToMany $query) use ($classroom) {
                $query->where('school_id', $classroom->school_id);
            }]);
        }

        if ($options['with_groups'] ?? false) {
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
     *     mastery_enabled: bool,
     *     self_rating_enabled: bool,
     *     gorups: array,
     * } $attributes
     *
     * @return Classroom
     *
     * @throws Throwable
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
            'mastery_enabled',
            'self_rating_enabled',
        ]);

        return DB::transaction(function () use ($attributes) {
            // Create the classroom.
            $classroom = Classroom::create($attributes);

            // Create the default classroom group.
            $classroom->defaultClassroomGroup()->create([
                'name' => $classroom->name . ' default group',
                'pass_grade' => $attributes['pass_grade'],
                'attempts' => $attributes['attempts'],
                'is_default' => true,
            ]);

            return $classroom;
        });
    }

    /**
     * Add secondary teachers to the given classroom.
     *
     * @param Classroom $classroom  The classroom to add secondary teachers to.
     * @param array     $teacherIds The IDs of the teachers to add.
     * @param bool      $detaching  Whether to detach the existing secondary teachers or not.
     *
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
     * @param Classroom $classroom  The classroom to remove secondary teachers from.
     * @param array     $teacherIds The IDs of the teachers to remove.
     *
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
     * @param array     $attributes
     *
     * @return Classroom
     *
     * @throws Throwable
     */
    public function update(Classroom $classroom, array $attributes): Classroom
    {
        DB::transaction(function () use ($classroom, $attributes) {
            // Update classroom.
            $classroom->update(Arr::only($attributes, [
                'year_id',
                'owner_id',
                'name',
                'mastery_enabled',
                'self_rating_enabled',
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
     * Delete the given classroom and its groups.
     *
     * @param Classroom $classroom
     *
     * @return void
     *
     * @throws Throwable
     */
    public function delete(Classroom $classroom): void
    {
        DB::transaction(function () use ($classroom) {
            // Delete classroom groups.
            $classroom->classroomGroups()->delete();

            // Delete classroom.
            $classroom->delete();
        });
    }

}
