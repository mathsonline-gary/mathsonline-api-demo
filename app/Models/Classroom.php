<?php

namespace App\Models;

use App\Enums\EnumClassroomType;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classroom extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => EnumClassroomType::class
    ];

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
        return $this->belongsTo(Teacher::class, 'owner_id')
            ->where('school_id', $this->school_id);
    }

    /**
     * Get the secondary teachers associated with the classroom.
     *
     * @return BelongsToMany
     */
    public function secondaryTeachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'classroom_secondary_teacher', 'classroom_id', 'teacher_id')
            ->where('school_id', $this->school_id);
    }
}
