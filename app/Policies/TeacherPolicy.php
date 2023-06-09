<?php

namespace App\Policies;

use App\Models\Users\Teacher;
use App\Models\Users\User;

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
     * @param Teacher $teacher The teacher is to be deleted.
     * @return bool
     */
    public function delete(User $user, Teacher $teacher): bool
    {
        return $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $teacher->school_id &&
            !$teacher->isClassroomOwner();
    }
}
