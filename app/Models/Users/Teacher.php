<?php

namespace App\Models\Users;

use App\Models\Classroom;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends User
{
    use HasFactory;

    protected $hidden = [
        'password',
    ];

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
        return $this->hasMany(Classroom::class, 'owner_id')
            ->where('school_id', $this->school_id);
    }

    /**
     * Get the classrooms where the teacher is a secondary teacher.
     *
     * @return BelongsToMany
     */
    public function classroomsAsSecondaryTeacher(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_secondary_teacher', 'teacher_id', 'classroom_id')
            ->where('school_id', $this->school_id)
            ->withTimestamps();
    }
}
