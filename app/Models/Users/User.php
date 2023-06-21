<?php

namespace App\Models\Users;

use App\Models\Action;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * Get all the user's actions.
     *
     * @return MorphMany
     */
    public function actions(): MorphMany
    {
        return $this->morphMany(Action::class, 'actionable');
    }

    /**
     * Get the user as a teacher.
     *
     * @return Teacher|null
     */
    public function asTeacher(): ?Teacher
    {
        return $this instanceof Teacher ? $this : null;
    }

    /**
     * Get the user as a student.
     *
     * @return Student|null
     */
    public function asStudent(): ?Student
    {
        return $this instanceof Student ? $this : null;
    }

    /**
     * Get the user as a tutor.
     *
     * @return Tutor|null
     */
    public function asTutor(): ?Tutor
    {
        return $this instanceof Tutor ? $this : null;
    }

    /**
     * Get the user as an admin.
     *
     * @return Admin|null
     */
    public function asAdmin(): ?Admin
    {
        return $this instanceof Admin ? $this : null;
    }

    /**
     * Get the user as an developer.
     *
     * @return Developer|null
     */
    public function asDeveloper(): ?Developer
    {
        return $this instanceof Developer ? $this : null;
    }
}
