<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Illuminate\Auth\Access\Response;

class ClassroomGroupPolicy
{
    public function update(User $user, Classroom $classroom): Response|bool
    {
        // The user is a teacher, and the classroom is a traditional classroom.
        /** @var Teacher $teacher */
        if ($user->isTeacher() && $classroom->isTraditionalClassroom()) {
            $teacher = $user->asTeacher();

            // The user is an admin teacher, and is update classroom groups of a traditional classroom in the same school.
            if ($teacher->isAdmin() && $teacher->school_id === $classroom->school_id) {
                return true;
            }

            // The user is a non-admin teacher, and is update classroom groups of a traditional classroom owned by him.
            if (!$teacher->isAdmin() && $teacher->id === $classroom->owner_id) {
                return true;
            }
        }

        return false;
    }
}
