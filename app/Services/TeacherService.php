<?php

namespace App\Services;

use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class TeacherService
{
    /**
     * Find a teacher record by ID with additional options.
     *
     * @param int $id
     * @param array{
     *     throwable?: bool,
     *     with_school?: bool,
     *     with_classrooms?: bool
     * } $options
     * @return Teacher|null
     */
    public function find(int $id, array $options = []): ?Teacher
    {
        $teacher = $options['throwable'] ?? true
            ? Teacher::findOrFail($id)
            : Teacher::find($id);

        if ($options['with_school'] ?? false) {
            $teacher->load('school');
        }

        if ($options['with_classrooms'] ?? false) {
            $teacher->load(['classroomsAsOwner' => function (HasMany $query) use ($teacher) {
                $query->where('school_id', $teacher->school_id);
            }])->load(['classroomsAsSecondaryTeacher' => function (BelongsToMany $query) use ($teacher) {
                $query->where('school_id', $teacher->school_id);
            }]);
        }

        return $teacher;
    }

    /**
     * Search teachers by options.
     *
     * @param array{
     *     school_id?: int,
     *     pagination?: bool
     * } $options
     * @return LengthAwarePaginator|Collection<Teacher>
     */
    public function search(array $options = []): Collection|LengthAwarePaginator
    {
        $query = Teacher::with([
            'classroomsAsOwner',
            'classroomsAsSecondaryTeacher',
        ]);

        if (isset($options['school_id'])) {
            $query = $query->where(['school_id' => $options['school_id']]);
        }

        return $options['pagination'] ?? true
            ? $query->paginate()
            : $query->get();
    }

    /**
     * Create a teacher by given attributes.
     *
     * @param array $attributes
     * @return Teacher
     */
    public function create(array $attributes): Teacher
    {
        $attributes = Arr::only($attributes, [
            'school_id',
            'username',
            'email',
            'password',
            'first_name',
            'last_name',
            'title',
            'position',
            'is_admin',
        ]);

        $teacher = new Teacher([
            ...$attributes,
            'password' => Hash::make($attributes['password']),
        ]);

        $teacher->is_admin = $attributes['is_admin'];

        $teacher->save();

        return $teacher;
    }
}
