<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'login',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Determine if the user is a tutor.
     *
     * @return bool
     */
    public function isTutor(): bool
    {
        return $this->type === 'tutor';
    }

    /**
     * Check if the user is a teacher.
     *
     * @return bool
     */
    public function isTeacher(): bool
    {
        return $this->type === 'teacher';
    }

    /**
     * Check if the user is a student.
     *
     * @return bool
     */
    public function isStudent(): bool
    {
        return $this->type === 'student';
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    /**
     * Check if the user is a developer.
     *
     * @return bool
     */
    public function isDeveloper(): bool
    {
        return $this->type === 'developer';
    }

    /**
     * Get the tutor associated with the user.
     *
     * @return HasOne
     */
    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class);
    }

    /**
     * Get the teacher associated with the user.
     *
     * @return HasOne
     */
    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Get the student associated with the user.
     *
     * @return HasOne
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the admin associated with the user.
     *
     * @return HasOne
     */
    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Get the developer associated with the user.
     *
     * @return HasOne
     */
    public function developer(): HasOne
    {
        return $this->hasOne(Developer::class);
    }

    /**
     * Get the associated Tutor model for the user.
     *
     * @return HasOne
     * @throws ModelNotFoundException
     */
    public function asTutor(): HasOne
    {
        if ($this->isTutor() && $this->tutor !== null) {
            return $this->tutor();
        }

        throw (new ModelNotFoundException)->setModel(Tutor::class);
    }

    /**
     * Get the associated Teacher model for the user.
     *
     * @return HasOne
     * @throws ModelNotFoundException
     */
    public function asTeacher(): HasOne
    {
        if ($this->isTeacher() && $this->teacher !== null) {
            return $this->teacher();
        }

        throw (new ModelNotFoundException)->setModel(Teacher::class);
    }

    /**
     * Get the associated Student model for the user.
     *
     * @return HasOne
     * @throws ModelNotFoundException
     */
    public function asStudent(): HasOne
    {
        if ($this->isStudent() && $this->student !== null) {
            return $this->student();
        }

        throw (new ModelNotFoundException)->setModel(Student::class);
    }

    /**
     * Get the associated Admin model for the user.
     *
     * @return HasOne
     * @throws ModelNotFoundException
     */
    public function asAdmin(): HasOne
    {
        if ($this->isAdmin() && $this->admin !== null) {
            return $this->admin();
        }

        throw (new ModelNotFoundException)->setModel(Admin::class);
    }

    /**
     * Get the associated Developer model for the user.
     *
     * @return HasOne
     * @throws ModelNotFoundException
     */
    public function asDeveloper(): HasOne
    {
        if ($this->isDeveloper() && $this->developer !== null) {
            return $this->developer();
        }

        throw (new ModelNotFoundException)->setModel(Developer::class);
    }

}
