<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Auth\Access\Response;

class ClassroomPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user instanceof Teacher;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Classroom $classroom): Response
    {
        // The user is an admin teacher, and viewing a classroom in his school.
        $condition1 = $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $classroom->school_id;

        // The user is a non-admin teacher, and viewing a classroom that he owns.
        $condition2 = $user instanceof Teacher &&
            !$user->isAdmin() &&
            $user->id === $classroom->owner_id;

        return ($condition1 || $condition2)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create classrooms.
     */
    public function create(User $user, Teacher $owner): bool
    {
        // The user is an admin teacher, and belongs to the same school as the classroom owner.
        $condition1 = $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $owner->school_id;

        // The user is a non-admin teacher, and is the owner of the classroom.
        $condition2 = $user instanceof Teacher &&
            !$user->isAdmin() &&
            $user->id === $owner->id;

        return $condition1 || $condition2;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Classroom $classroom): bool
    {
        // The user is an admin teacher, and is updating a classroom in the same school.
        $condition1 = $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $classroom->school_id;

        // The user is a non-admin teacher, and is updating a classroom owned by him.
        $condition2 = $user instanceof Teacher &&
            !$user->isAdmin() &&
            $user->id === $classroom->owner_id;

        return $condition1 || $condition2;
    }

    /**
     * Determine whether the user can delete the classroom.
     */
    public function delete(User $user, Classroom $classroom): bool
    {
        // The user is an admin teacher, and deleting the classroom in his school.
        $condition1 = $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $classroom->school_id;

        // The user is a non-admin teacher, and deleting the classroom that he owns.
        $condition2 = $user instanceof Teacher &&
            !$user->isAdmin() &&
            $classroom->owner_id === $user->id;

        return $condition1 || $condition2;
    }
}
