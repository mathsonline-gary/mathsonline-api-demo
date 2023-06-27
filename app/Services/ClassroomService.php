<?php

namespace App\Services;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ClassroomService
{
    /**
     * Search classrooms by options.
     *
     * @param array{
     *     school_id?: int,
     *     key?: string,
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
        });

        return $query->when($options['pagination'] ?? true, function (Builder $query) {
            return $query->paginate();
        }, function (Builder $query) {
            return $query->get();
        });
    }
}
