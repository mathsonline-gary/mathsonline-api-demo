<?php

namespace App\Models\Users;

use App\Models\Action;
use App\Models\Classroom;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Teacher extends User
{
    use HasFactory;

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'title',
        'position',
        'school_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_admin' => 'bool',
    ];

    /**
     * Identify if the teacher has the administrator access.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Get the school of the teacher.
     *
     * @return BelongsTo
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the classrooms owned by the teacher.
     *
     * @return HasMany
     */
    public function classroomsAsOwner(): HasMany
    {
        return $this->hasMany(Classroom::class, 'owner_id');
    }

    /**
     * Indicate that if the teacher owns any classroom.
     *
     * @return bool
     */
    public function isClassroomOwner(): bool
    {
        return $this->classroomsAsOwner()->count() > 0;
    }

    /**
     * Get the classrooms of which the teacher is a secondary teacher.
     *
     * @return BelongsToMany
     */
    public function classroomsAsSecondaryTeacher(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_secondary_teacher', 'teacher_id', 'classroom_id')
            ->withTimestamps();
    }

    /**
     * Indicate that if the teacher is a secondary teacher of any classroom.
     *
     * @return bool
     */
    public function isSecondaryTeacher(): bool
    {
        return $this->classroomsAsSecondaryTeacher()->count() > 0;
    }
}
