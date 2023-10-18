<?php

namespace App\Models;

use App\Models\Users\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassroomGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'pass_grade' => 'int',
        'attempts' => 'int',
        'is_default' => 'bool',
    ];

    protected $fillable = [
        'classroom_id',
        'name',
        'pass_grade',
        'attempts',
        'is_default',
    ];

    /**
     * Determine if the classroom group is the default group of the classroom.
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * Get the classroom associated with the classroom group.
     *
     * @return BelongsTo
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get the students associated with the classroom group.
     *
     * @return BelongsToMany
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withTimestamps();
    }
}
