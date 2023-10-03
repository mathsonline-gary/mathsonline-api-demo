<?php

namespace App\Policies;

use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Auth\Access\Response;

class TeacherPolicy
{
    /**
     * Determine whether the user can view any teachers.
     */
    public function viewAny(User $user): Response
    {
        if ($user->isTeacher() &&
            $user->asTeacher()->isAdmin()) {
            return Response::allow();
        }

        return Response::deny();
    }

    /**
     * Determine whether the user can view the teacher.
     */
    public function view(User $user, Teacher $teacher): Response
    {
        if ($user->isTeacher() &&
            $user->asTeacher()->isAdmin() &&
            $user->asTeacher()->school_id === $teacher->school_id) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create teachers.
     */
    public function create(User $user): Response
    {
        if ($user->isTeacher() &&
            $user->asTeacher()->isAdmin()) {
            return Response::allow();
        }

        return Response::deny();
    }

    /**
     * Determine whether the user can update the teacher.
     */
    public function update(User $user, Teacher $teacher): Response
    {
        // User is a teacher, and is updating personal profile.
        if ($user->isTeacher() &&
            $user->asTeacher()->id === $teacher->id) {
            return Response::allow();
        }

        // User is an admin teacher, and is updating a teacher in the same school.
        if ($user->isTeacher() &&
            $user->asTeacher()->isAdmin() &&
            $user->asTeacher()->school_id === $teacher->school_id) {
            return Response::allow();
        }

        return Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the teacher.
     */
    public function delete(User $user, Teacher $teacher): Response
    {
        if ($user->isTeacher() &&
            $user->asTeacher()->isAdmin() &&
            $user->asTeacher()->school_id === $teacher->school_id &&
            $user->asTeacher()->id !== $teacher->id) {
            return Response::allow();
        }

        return Response::deny();
    }
}
