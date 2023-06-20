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
}
