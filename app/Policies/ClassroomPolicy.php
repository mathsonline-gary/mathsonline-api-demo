<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\Users\User;

class ClassroomPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isTeacher()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Classroom $classroom): bool
    {
        if ($teacher = $user->asTeacher()) {
            // The user is an admin teacher, and viewing a classroom in his school.
            if ($teacher->isAdmin() && $teacher->school_id === $classroom->school_id) {
                return true;
            }

            // The user is a non-admin teacher, and viewing a classroom that he owns.
            if (!$teacher->isAdmin() && $teacher->id === $classroom->owner_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create classrooms.
     */
    public function create(User $user): bool
    {
        // The user is a teacher.
        if ($user->isTeacher()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Classroom $classroom): bool
    {
        if ($teacher = $user->asTeacher()) {
            // The user is an admin teacher, and viewing a classroom in his school.
            if ($teacher->isAdmin() && $teacher->school_id === $classroom->school_id) {
                return true;
            }

            // The user is a non-admin teacher, and viewing a classroom that he owns.
            if (!$teacher->isAdmin() && $teacher->id === $classroom->owner_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the classroom.
     */
    public function delete(User $user, Classroom $classroom): bool
    {
        if ($teacher = $user->asTeacher()) {
            // The user is an admin teacher, and deleting the classroom in his school.
            if ($teacher->isAdmin() && $teacher->school_id === $classroom->school_id) {
                return true;
            }

            // The user is a non-admin teacher, and deleting the classroom that he owns.
            if (!$teacher->isAdmin() && $teacher->id === $classroom->owner_id) {
                return true;
            }
        }

        return false;
    }
}
