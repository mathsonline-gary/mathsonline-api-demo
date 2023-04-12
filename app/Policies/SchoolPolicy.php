<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, School $school): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, School $school): bool
    {
    }

    public function delete(User $user, School $school): bool
    {
    }

    public function restore(User $user, School $school): bool
    {
    }

    public function forceDelete(User $user, School $school): bool
    {
    }
}
