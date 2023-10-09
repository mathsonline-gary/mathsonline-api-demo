<?php

namespace App\Models;

use App\Enums\ClassroomType;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'owner_id',
        'year_id',
        'type',
        'name',
        'mastery_enabled',
        'self_rating_enabled',
    ];

    protected $casts = [
        'school_id' => 'int',
        'owner_id' => 'int',
        'year_id' => 'int',
        'type' => ClassroomType::class,
        'mastery_enabled' => 'bool',
        'self_rating_enabled' => 'bool',
    ];

    // The max limit of the number of classroom groups.
    public const MAX_CUSTOM_GROUP_COUNT = 8;

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
     * Get the classroom groups of the classroom.
     *
     * @return HasMany
     */
    public function classroomGroups(): HasMany
    {
        return $this->hasMany(ClassroomGroup::class);
    }

    /**
     * Get the default classroom group of the classroom.
     *
     * @return HasOne
     */
    public function defaultClassroomGroup(): HasOne
    {
        return $this->hasOne(ClassroomGroup::class)
            ->where('is_default', true);
    }

    /**
     * Get the custom classroom groups of the classroom.
     *
     * @return HasMany
     */
    public function customClassroomGroups(): HasMany
    {
        return $this->hasMany(ClassroomGroup::class)
            ->where('is_default', false);
    }
}
