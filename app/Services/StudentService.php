<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\Users\Student;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentService
{
    /**
     * @param array{
     *     school_id?: int,
     *     classroom_ids?: array<int>,
     *     with_school?: bool,
     *     with_activities?: bool,
     *     with_classroom_groups?: bool,
     *     with_classrooms?: bool,
     *     key?: string,
     *     pagination?: bool,
     *     page?: int,
     *     per_page?: int,
     * } $options
     *
     * @return Collection<Student>|LengthAwarePaginator
     */
    public function search(array $options = []): Collection|LengthAwarePaginator
    {
        $searchKey = $options['key'] ?? null;

        return Student::when($options['with_school'] ?? false, function (Builder $query) {
            $query->with('school');
        })
            ->when($options['with_activities'] ?? false, function (Builder $query) {
                $query->with('user.activities');
            })
            ->when($options['with_classroom_groups'] ?? false, function (Builder $query) {
                $query->with('classroomGroups');
            })
            ->when($options['with_classrooms'] ?? false, function (Builder $query) {
                $query->with('classroomGroups.classroom');
            })
            ->when(isset($options['school_id']), function (Builder $query) use ($options) {
                $query->where('school_id', $options['school_id']);
            })
            ->when(isset($options['classroom_ids']), function (Builder $query) use ($options) {
                $query->whereHas('classroomGroups', function (Builder $query) use ($options) {
                    $query->whereIn('classroom_id', $options['classroom_ids']);
                });
            })
            ->when($searchKey && $searchKey !== '', function (Builder $query) use ($searchKey) {
                $query->where(function (Builder $query) use ($searchKey) {
                    $query->where('username', 'like', "%$searchKey%")
                        ->orWhere('first_name', 'like', "%$searchKey%")
                        ->orWhere('last_name', 'like', "%$searchKey%")
                        ->orWhereHas('classroomGroups.classroom', function (Builder $query) use ($searchKey) {
                            $query->where('name', 'like', "%$searchKey%");
                        });
                });
            })
            ->when($options['pagination'] ?? true, function (Builder $query) use ($options) {
                return $query->paginate($options['per_page'] ?? 20)->withQueryString();
            }, function (Builder $query) {
                return $query->get();
            });
    }

    /**
     * @param int $id
     * @param array{
     *     throwable?: bool,
     *     with_school?: bool,
     *     with_classrooms?: bool,
     *     with_activities?: bool,
     *     } $options
     * @return Student|null
     */
    public function find(int $id, array $options = []): ?Student
    {
        $student = Student::when($options['throwable'] ?? true, function (Builder $query) use ($id) {
            return $query->findOrFail($id);
        }, function (Builder $query) use ($id) {
            return $query->find($id);
        });


        if ($options['with_school'] ?? false) {
            $student->load('school');
        }

        if ($options['with_classrooms'] ?? false) {
            $student->load('classroomGroups', 'classroomGroups.classroom')
                ->whereHas('classroomGroups.classroom', function ($query) use ($student) {
                    $query->where('school_id', $student->school_id);
                });
        }

        if ($options['with_activities'] ?? false) {
            $student->load('user.activities');
        }

        return $student;
    }

    /**
     * Create a student with the given attributes.
     *
     * @param array $attributes
     * @return Student
     */
    public function create(array $attributes): Student
    {
        $attributes = Arr::only($attributes, [
            'school_id',
            'username',
            'email',
            'password',
            'first_name',
            'last_name',
            'settings',
        ]);

        return DB::transaction(function () use ($attributes) {
            // Create a user.
            $user = User::create([
                'login' => $attributes['username'],
                'email' => $attributes['email'] ?? null,
                'password' => Hash::make($attributes['password']),
                'type' => UserType::STUDENT,
            ]);

            // Create the student.
            $student = new Student($attributes);
            $user->student()->save($student);

            // Create the student settings.
            $student->settings()->create($attributes['settings'] ?? []);

            return $student;
        });
    }

    /**
     * Update a student.
     *
     * @param Student $student
     * @param array $payload
     * @return Student
     */
    public function update(Student $student, array $payload): Student
    {
        return DB::transaction(function () use ($student, $payload) {
            // Update the student.
            {
                $fillableStudentAttributes = Arr::only($payload, [
                    'username',
                    'email',
                    'first_name',
                    'last_name',
                ]);

                if (count($fillableStudentAttributes) > 0) {
                    $student->update($fillableStudentAttributes);
                }
            }

            // Update the associated user.
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
                    $student->user()->update($fillableUserAttributes);
                }
            }

            // Update the associated student settings.
            {
                $fillableSettingsAttributes = Arr::only($payload, [
                    'confetti_enabled',
                ]);

                if (count($fillableSettingsAttributes) > 0) {
                    $student->settings()->update($fillableSettingsAttributes);
                }
            }

            return $student;
        });
    }

    /**
     * Soft delete a student.
     *
     * @param Student|int $student
     * @return void
     */
    public function delete(Student|int $student): void
    {
        DB::transaction(function () use ($student) {
            if (is_int($student)) {
                $student = Student::findOrFail($student);
            }

            // Detach the student from all classroom groups.
            $student->classroomGroups()->detach();

            // Soft delete the student.
            $student->delete();
        });
    }

    /**
     * Assign the given student into the given classroom groups.
     *
     * @param Student $student
     * @param array $classroomGroupIds
     * @param array{
     *     expired_tasks_excluded?: bool,
     *     detaching?: bool,
     * } $options
     * @return void
     */
    public function addToClassroomGroups(Student $student, array $classroomGroupIds, array $options = []): void
    {
        $classroomGroups = [];

        foreach ($classroomGroupIds as $classroomGroupId) {
            $classroomGroups[$classroomGroupId] = [
                'expired_tasks_excluded' => $options['expired_tasks_excluded'] ?? true,
            ];
        }

        if ($options['detaching'] ?? true) {
            $student->classroomGroups()->sync($classroomGroups);
        } else {
            if (count($classroomGroups) > 0) {
                $student->classroomGroups()->syncWithoutDetaching($classroomGroups);
            }
        }
    }
}
