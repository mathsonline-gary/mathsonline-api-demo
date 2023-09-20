<?php

namespace App\Services;

use App\Models\Users\Student;
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
     *     key?: string,
     *     pagination?: bool
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
                        ->orWhere('last_name', 'like', "%$searchKey%");
                });
            })
            ->when($options['pagination'] ?? true, function (Builder $query) {
                return $query->paginate()->withQueryString();
            }, function (Builder $query) {
                return $query->get();
            });
    }

    /**
     * @param int $id
     * @param array{
     *     throwable?: bool,
     *     with_school?: bool,
     *     with_classroom_groups?: bool
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

        if ($options['with_classroom_groups'] ?? false) {
            $student->load('classroomGroups', 'classroomGroups.classroom')
                ->whereHas('classroomGroups.classroom', function ($query) use ($student) {
                    $query->where('school_id', $student->school_id);
                });
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
        ]);

        $student = new Student([
            ...$attributes,
            'password' => Hash::make($attributes['password']),
        ]);

        $student->save();

        return $student;
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
        $fillableAttributes = Arr::only($payload, [
            'username',
            'email',
            'first_name',
            'last_name',
        ]);

        if (isset($payload['password'])) {
            $fillableAttributes['password'] = Hash::make($payload['password']);
        }

        $student->fill($fillableAttributes);

        $student->save();

        return $student;
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
}
