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
     * }          $options
     *
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
     *     pagination?: bool,
     *     per_page?: int,
     *     with_school?: bool,
     *     with_classrooms?: bool
     * } $options
     *
     * @return LengthAwarePaginator|Collection<Teacher>
     */
    public function search(array $options = []): Collection|LengthAwarePaginator
    {
        $searchKey = $options['key'] ?? null;

        $query = Teacher::when($options['with_school'] ?? false, function (Builder $query) {
            $query->with('school');
        })
            ->when($options['with_classrooms'] ?? false, function (Builder $query) {
                $query->with(['ownedClassrooms', 'secondaryClassrooms']);
            })
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
            ? $query->paginate($options['per_page'] ?? 20)->withQueryString()
            : $query->get();
    }

    /**
     * Create a teacher by given attributes.
     *
     * @param array $attributes
     *
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
                'email' => $attributes['email'] ?? null,
                'password' => Hash::make($attributes['password']),
                'type' => User::TYPE_TEACHER,
            ]);

            // Create a teacher.
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
     * @param array   $payload
     *
     * @return Teacher
     */
    public function update(Teacher $teacher, array $payload): Teacher
    {
        return DB::transaction(function () use ($teacher, $payload) {
            // Update teacher attributes.
            {
                $fillableAttributes = Arr::only($payload, [
                    'username',
                    'email',
                    'first_name',
                    'last_name',
                    'title',
                    'position',
                ]);

                // Update massive assignable attributes.
                $teacher->fill($fillableAttributes);

                // Safely update attribute "is_admin".
                $teacher->is_admin = $payload['is_admin'] ?? $teacher->is_admin;

                $teacher->save();
            }

            // Update associated user credentials.
            {
                $fillableUserAttributes = [];

                if (isset($payload['username'])) {
                    $fillableUserAttributes['login'] = $payload['username'];
                }
                if (isset($payload['email'])) {
                    $fillableUserAttributes['email'] = $payload['email'];
                }
                if (isset($payload['password'])) {
                    $fillableUserAttributes['password'] = Hash::make($payload['password']);
                }

                if (count($fillableUserAttributes) > 0) {
                    $teacher->user()->update($fillableUserAttributes);
                }
            }

            return $teacher->refresh();
        });
    }
}
