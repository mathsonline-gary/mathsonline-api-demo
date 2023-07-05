<?php

namespace App\Policies;

use App\Models\Users\Teacher;
use App\Models\Users\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user instanceof Teacher;
    }
}
