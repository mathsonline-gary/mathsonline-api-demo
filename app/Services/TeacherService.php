<?php

namespace App\Services;

use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        $teacher = Teacher::when($options['throwable'] ?? true, function (Builder $query) use ($id) {
            return $query->findOrFail($id);
        }, function (Builder $query) use ($id) {
            return $query->find($id);
        });

        if ($options['with_school'] ?? false) {
            $teacher->load('school');
        }

        if ($options['with_classrooms'] ?? false) {
            $teacher->load(['ownedClassrooms' => function (HasMany $query) use ($teacher) {
                $query->where('school_id', $teacher->school_id);
            }])->load(['secondaryClassrooms' => function (BelongsToMany $query) use ($teacher) {
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
     *     key?: string,
     *     pagination?: bool
     * } $options
     * @return LengthAwarePaginator|Collection<Teacher>
     */
    public function search(array $options = []): Collection|LengthAwarePaginator
    {
        $searchKey = $options['key'] ?? null;

        $query = Teacher::with([
            'school',
            'ownedClassrooms',
            'secondaryClassrooms',
        ])
            ->when($options['school_id'] ?? false, function (Builder $query) use ($options) {
                $query->where(['school_id' => $options['school_id']]);
            })
            ->when($searchKey && $searchKey !== '', function (Builder $query) use ($searchKey) {
                $query->where(function (Builder $query) use ($searchKey) {
                    $query->where('username', 'like', "%$searchKey%")
                        ->orWhere('first_name', 'like', "%$searchKey%")
                        ->orWhere('last_name', 'like', "%$searchKey%")
                        ->orWhere('email', 'like', "%$searchKey%");
                });
            });

        return $options['pagination'] ?? true
            ? $query->paginate()->withQueryString()
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

        return DB::transaction(function () use ($attributes) {
            // Create a user.
            $user = User::create([
                'login' => $attributes['username'],
                'password' => Hash::make($attributes['password']),
                'type_id' => User::TYPE_TEACHER,
            ]);

            $teacher = new Teacher($attributes);

            $teacher->is_admin = $attributes['is_admin'];

            $user->teacher()->save($teacher);

            return $teacher;
        });
    }

    /**
     * Soft-delete teacher for the given ID.
     *
     * @param Teacher $teacher The teacher to be deleted.
     *
     * @return void
     */
    public function delete(Teacher $teacher): void
    {
        DB::transaction(function () use ($teacher) {
            // Detach the teacher from secondary teacher list.
            $teacher->secondaryClassrooms()->detach();

            // Dissociate the teacher from the owner of classrooms.
            $teacher->ownedClassrooms()->update(['owner_id' => null]);

            // Delete the teacher.
            $teacher->delete();

            // Delete the user.
            $teacher->user()->delete();
        });
    }

    /**
     * Update a teacher with given valid attributes.
     *
     * @param Teacher $teacher
     * @param array $attributes
     * @return Teacher
     */
    public function update(Teacher $teacher, array $attributes): Teacher
    {
        $fillableAttributes = Arr::only($attributes, [
            'username',
            'email',
            'password',
            'first_name',
            'last_name',
            'title',
            'position',
        ]);

        if (isset($fillableAttributes['password'])) {
            $fillableAttributes['password'] = Hash::make($fillableAttributes['password']);
        }

        // Update massive assignable attributes.
        $teacher->fill($fillableAttributes);

        // Safely update attribute "is_admin".
        $teacher->is_admin = $attributes['is_admin'] ?? $teacher->is_admin;

        $teacher->save();

        return $teacher;
    }
}
