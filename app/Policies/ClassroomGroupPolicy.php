<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\ClassroomGroup;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Auth\Access\Response;

class ClassroomGroupPolicy
{
    public function create(User $user, Classroom $classroom): bool
    {
        // The user is an admin teacher, and is creating a group of a classroom in the same school.
        $condition1 = $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $classroom->school_id;

        // The user is a non-admin teacher, and is creating a group of a classroom owned by him.
        $condition2 = $user instanceof Teacher &&
            !$user->isAdmin() &&
            $user->id === $classroom->owner_id;

        return $condition1 || $condition2;
    }

    public function update(User $user, ClassroomGroup $classroomGroup, Classroom $classroom): Response|bool
    {
        if ($classroomGroup->classroom_id !== $classroom->id) {
            return Response::denyAsNotFound();
        }

        // The user is an admin teacher, and is creating a group of a classroom in the same school.
        $condition1 = $user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $classroom->school_id;

        // The user is a non-admin teacher, and is creating a group of a classroom owned by him.
        $condition2 = $user instanceof Teacher &&
            !$user->isAdmin() &&
            $user->id === $classroom->owner_id;

        return $condition1 || $condition2;
    }
}
