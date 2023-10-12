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

    /**
     * Determine whether the user can add secondary teachers to the classroom.
     */
    public function addSecondaryTeacher(User $user, Classroom $classroom): bool
    {
        // The user is a teacher.
        if ($teacher = $user->asTeacher()) {
            // The user is an admin teacher, and managing a traditional classroom in his school.
            if ($teacher->isAdmin() &&
                $classroom->isTraditionalClassroom() &&
                $teacher->school_id === $classroom->school_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can remove secondary teachers from the classroom.
     */
    public function removeSecondaryTeacher(User $user, Classroom $classroom, User $targetUser): bool
    {
        // The user is a teacher.
        if ($teacher = $user->asTeacher()) {
            // Condition: 1. The user is an admin teacher
            //            2. The classroom is a traditional classroom
            //            3. The classroom is in the same school as the teacher
            if ($teacher->isAdmin() &&
                $classroom->isTraditionalClassroom() &&
                $teacher->school_id === $classroom->school_id
            ) {
                $targetTeacher = $targetUser->asTeacher();

                if ($targetTeacher &&
                    $targetTeacher->school_id === $teacher->school_id &&
                    $targetTeacher->isSecondaryTeacherOfClassroom($classroom)
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
