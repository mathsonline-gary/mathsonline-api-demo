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
        if ($user->isTeacher()) {
            return true;
        }
        return false;
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->isTeacher()) {
            return $user->asTeacher()->canManageStudent($student);
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isTeacher()) {
            return true;
        }

        return false;
    }

    public function update(User $user, Student $student): bool
    {
        // The user ia an admin teacher, and the student is from the same school.
        if ($user->isTeacher()) {
            return $user->asTeacher()->canManageStudent($student);
        }

        return false;
    }

    public function delete(User $user, Student $student): Response
    {
        // The user ia an admin teacher, and the student is from the same school.
        if ($user instanceof Teacher &&
            $user->isAdmin()) {
            return $user->school_id === $student->school_id
                ? Response::allow()
                : Response::denyAsNotFound('No student found.');
        }

        return Response::deny();
    }
}
