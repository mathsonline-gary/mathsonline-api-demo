<?php

namespace App\Policies;

use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Auth\Access\Response;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user instanceof Teacher;
    }

    public function view(User $user, Student $student): Response
    {
        // The user is an admin teacher, and the student is from the same school.
        if ($user instanceof Teacher &&
            $user->isAdmin() &&
            $user->school_id === $student->school_id) {
            return Response::allow();
        }

        // The user is a non-admin teacher, and the student is from the same school and from a classroom that they manage.
        if ($user instanceof Teacher &&
            !$user->isAdmin() &&
            $user->school_id === $student->school_id &&
            $student->classroomGroups()
                ->whereIn('classroom_id',
                    $user->getOwnedAndSecondaryClassrooms()
                        ->pluck('id')
                        ->toArray())
                ->exists()) {
            return Response::allow();
        }

        return Response::denyAsNotFound('No student found.');
    }
}
