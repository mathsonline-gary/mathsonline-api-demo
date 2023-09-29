<?php

namespace App\Models\Users;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'login',
        'password',
        'type_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'type_id' => 'int',
    ];

    public $timestamps = false;

    public const TYPE_STUDENT = 1;
    public const TYPE_TEACHER = 2;
    public const TYPE_MEMBER = 3;
    public const TYPE_ADMIN = 4;
    public const TYPE_DEVELOPER = 5;

    /**
     * Get all the user's activities.
     *
     * @return HasMany
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Indicate the user to be a teacher.
     *
     * @return HasOne
     */
    public function asTeacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Indicate the user to be a student.
     *
     * @return HasOne
     */
    public function asStudent(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Indicate the user to be a member.
     *
     * @return HasOne
     */
    public function asMember(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    /**
     * Indicate the user to be an administrator.
     *
     * @return HasOne
     */
    public function asAdmin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Indicate the user to be a developer.
     *
     * @return HasOne
     */
    public function asDeveloper(): HasOne
    {
        return $this->hasOne(Developer::class);
    }
}
