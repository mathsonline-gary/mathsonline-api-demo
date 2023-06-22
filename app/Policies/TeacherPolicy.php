<?php

namespace App\Policies;

use App\Models\Users\Teacher;
use App\Models\Users\User;

class TeacherPolicy
{
    /**
     * Determine whether the user can view any teachers.
     */
    public function viewAny(User $user): bool
    {
        return $user instanceof Teacher &&
            $user->isAdmin();
    }

    /**
     * Determine whether the user can view the teacher.
     */
    public function view(User $user, Teacher $teacher): bool
    {
        return $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $teacher->school_id;
    }

    /**
     * Determine whether the user can create teachers in the same school.
     */
    public function create(User $user, int $schoolId): bool
    {
        return $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $schoolId;
    }

    /**
     * Determine whether the user can update the teacher.
     */
    public function update(User $user, Teacher $teacher): bool
    {
        // User is a teacher, and is updating personal profile.
        $condition1 = $user instanceof Teacher &&
            $user->id === $teacher->id;

        // User is an admin teacher, and is updating a teacher in the same school.
        $condition2 = $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $teacher->school_id;

        return $condition1 || $condition2;
    }

    /**
     * Determine whether the user can delete the teacher.
     */
    public function delete(User $user, Teacher $teacher): bool
    {
        return $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $teacher->school_id &&
            !$teacher->isClassroomOwner();
    }
}
