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
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Classroom $classroom): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Classroom $classroom): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Classroom $classroom): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Classroom $classroom): bool
    {
        //
    }
}
