<?php

namespace App\Models\Users;

use App\Concerns\Authenticatable;
use App\Models\ClassroomGroup;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory,
        SoftDeletes,
        Authenticatable;

    protected $fillable = [
        'school_id',
        'username',
        'email',
        'first_name',
        'last_name',
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
            ->withTimestamps();
    }
}
