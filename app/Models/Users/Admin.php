<?php

namespace App\Models\Users;

use App\Models\Action;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Admin extends User
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'username',
        'email',
        'first_name',
        'last_name',
        'password'
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get all the admin's actions.
     *
     * @return MorphMany
     */
    public function actions(): MorphMany
    {
        return $this->morphMany(Action::class, 'actionable');
    }
}
