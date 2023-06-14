<?php

namespace App\Models\Users;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Developer extends User
{
    use HasFactory;

    protected $fillable = [
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
     * Get all the developer's activities.
     *
     * @return MorphMany
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'actionable');
    }
}
