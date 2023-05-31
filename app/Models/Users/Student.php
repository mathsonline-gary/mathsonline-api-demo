<?php

namespace App\Models\Users;

use App\Models\ClassroomGroup;
use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends User
{
    use HasFactory;

    protected $hidden = [
        'password',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the classroom groups associated with the student.
     *
     * @return BelongsToMany
     */
    public function classroomGroups(): BelongsToMany
    {
        return $this->belongsToMany(ClassroomGroup::class)
            ->whereHas('classroom', function (Builder $query) {
                $query->where('school_id', $this->school_id);
            });
    }
}
