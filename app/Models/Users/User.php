<?php

namespace App\Models\Users;

use App\Enums\UserType;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory,
        HasApiTokens,
        Notifiable,
        SoftDeletes;

    protected $fillable = [
        'login',
        'password',
        'type',
    ];

    protected $hidden = [
        'password',
        'oauth_google_id',
    ];

    protected $casts = [
        'type' => UserType::class,
    ];

    public $timestamps = false;

    /**
     * Get all the user's activities.
     *
     * @return HasMany
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'actor_id');
    }

    /**
     * Indicate the user to be a teacher.
     *
     * @return HasOne
     */
    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Indicate the user to be a student.
     *
     * @return HasOne
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Indicate the user to be a member.
     *
     * @return HasOne
     */
    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    /**
     * Indicate the user to be an administrator.
     *
     * @return HasOne
     */
    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Indicate the user to be a developer.
     *
     * @return HasOne
     */
    public function developer(): HasOne
    {
        return $this->hasOne(Developer::class);
    }


    /**
     * Determine whether the user is a teacher.
     *
     * @return bool
     */
    public function isTeacher(): bool
    {
        return $this->type === UserType::TEACHER && $this->teacher !== null;
    }

    /**
     * Get the user as a teacher.
     *
     * @return Teacher|null
     */
    public function asTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    /**
     * Determine whether the user is a student.
     *
     * @return bool
     */
    public function isStudent(): bool
    {
        return $this->type === UserType::STUDENT && $this->student !== null;
    }

    /**
     * Get the user as a student.
     *
     * @return Student|null
     */
    public function asStudent(): ?Student
    {
        return $this->student;
    }

    /**
     * Determine whether the user is a member.
     *
     * @return bool
     */
    public function isMember(): bool
    {
        return $this->type === UserType::MEMBER && $this->member !== null;
    }

    /**
     * Get the user as a member.
     *
     * @return Member|null
     */
    public function asMember(): ?Member
    {
        return $this->member;
    }

    /**
     * Determine whether the user is an administrator.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->type === UserType::ADMIN && $this->admin !== null;
    }

    /**
     * Get the user as an administrator.
     *
     * @return Admin|null
     */
    public function asAdmin(): ?Admin
    {
        return $this->admin;
    }

    /**
     * Determine whether the user is a developer.
     *
     * @return bool
     */
    public function isDeveloper(): bool
    {
        return $this->type === UserType::DEVELOPER && $this->developer !== null;
    }

    /**
     * Get the user as a developer.
     *
     * @return Developer|null
     */
    public function asDeveloper(): ?Developer
    {
        return $this->developer;
    }
}
