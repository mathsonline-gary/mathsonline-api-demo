<?php

namespace App\Policies;

use App\Models\School;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Auth\Access\Response;

class TeacherPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view any teacher in given school.
     *
     * @param User $user
     * @param int $schoolId
     * @return bool
     */
    public function viewAnyInSchool(User $user, int $schoolId): bool
    {
        return $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $schoolId;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Teacher $teacher): bool
    {
        return $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $teacher->school_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, int $schoolId): bool
    {
        return $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $schoolId;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Teacher $teacher): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user The current authenticated user.
     * @param array<int> $teacherIds The list of IDs of teachers to be deleted.
     *
     * @return bool
     */
    public function delete(User $user, array $teacherIds): bool
    {
        // Get the school IDs of the teachers to be deleted
        $teacherSchoolIds = Teacher::whereIn('id', $teacherIds)
            ->pluck('school_id')
            ->toArray();
        
        return count($teacherIds) > 0 &&
            count($teacherSchoolIds) > 0 &&
            $user instanceof Teacher &&
            $user->isAdmin() &&
            count(array_unique($teacherSchoolIds)) === 1 &&
            $user->school_id === $teacherSchoolIds[0];
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Teacher $teacher): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Teacher $teacher): bool
    {
        return true;
    }
}
