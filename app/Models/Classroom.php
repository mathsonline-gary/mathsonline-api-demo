<?php

namespace App\Models;

use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'owner_id',
        'type',
        'name',
        'pass_grade',
        'attempts',
    ];

    public const TRADITIONAL_CLASSROOM = 'traditional classroom';

    public const HOMESCHOOL_CLASSROOM = 'homeschool classroom';

    /**
     * Get the school associated with the classroom.
     *
     * @return BelongsTo
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the owner (teacher) of the classroom.
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'owner_id');
    }

    /**
     * Get the secondary teachers associated with the classroom.
     *
     * @return BelongsToMany
     */
    public function secondaryTeachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'classroom_secondary_teacher', 'classroom_id', 'teacher_id')
            ->withTimestamps();
    }

    /**
     * Get the classroom groups for the class.
     *
     * @return HasMany
     */
    public function classroomGroups(): HasMany
    {
        return $this->hasMany(ClassroomGroup::class);
    }
}
