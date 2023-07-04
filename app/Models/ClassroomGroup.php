<?php

namespace App\Models;

use App\Models\Users\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClassroomGroup extends Model
{
    use HasFactory;

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
