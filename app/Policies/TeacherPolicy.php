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
        if ($user->isTeacher() &&
            $user->asTeacher()->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the teacher.
     */
    public function view(User $user, Teacher $teacher): bool
    {
        if ($user->isTeacher() &&
            $user->asTeacher()->isAdmin() &&
            $user->asTeacher()->school_id === $teacher->school_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create teachers.
     */
    public function create(User $user): bool
    {
        if ($user->isTeacher() &&
            $user->asTeacher()->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the teacher.
     */
    public function update(User $user, Teacher $teacher): bool
    {
        // User is a teacher, and is updating personal profile.
        if ($user->isTeacher() &&
            $user->asTeacher()->id === $teacher->id) {
            return true;
        }

        // User is an admin teacher, and is updating a teacher in the same school.
        if ($user->isTeacher() &&
            $user->asTeacher()->isAdmin() &&
            $user->asTeacher()->school_id === $teacher->school_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the teacher.
     */
    public function delete(User $user, Teacher $teacher): bool
    {
        if ($user->isTeacher() &&
            $user->asTeacher()->isAdmin() &&
            $user->asTeacher()->school_id === $teacher->school_id &&
            $user->asTeacher()->id !== $teacher->id) {
            return true;
        }

        return false;
    }
}
