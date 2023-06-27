<?php

namespace App\Services;

use App\Models\Classroom;
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
     *     pagination?: bool
     * } $options
     * @return Collection|LengthAwarePaginator
     */
    public function search(array $options = []): Collection|LengthAwarePaginator
    {
        $searchKey = $options['key'] ?? null;

        $query = Classroom::query();

        if (isset($options['school_id'])) {
            $query = $query->where(['school_id' => $options['school_id']]);
        }

        if ($searchKey && $searchKey !== '') {
            $query = $query->where('name', 'like', "%$searchKey%");
        }

        return $options['pagination'] ?? true
            ? $query->paginate()
            : $query->get();
    }
}
