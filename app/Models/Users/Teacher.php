<?php

namespace App\Models\Users;

use App\Concerns\HasCredentials;
use App\Models\Classroom;
use App\Models\School;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory,
        SoftDeletes,
        HasCredentials;

    protected $fillable = [
        'username',
        'email',
        'first_name',
        'last_name',
        'title',
        'position',
        'school_id',
    ];

    protected $casts = [
        'is_admin' => 'bool',
    ];

    /**
     * Get the authentication credentials of the teacher.
     *
     * @return BelongsTo
     */
    public function credentials(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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
    public function ownedClassrooms(): HasMany
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
        return $this->ownedClassrooms()->count() > 0;
    }

    /**
     * Indicate that if the teacher owns the given classroom.
     *
     * @param Classroom $classroom
     * @return bool
     */
    public function isOwnerOfClassroom(Classroom $classroom): bool
    {
        return $this->ownedClassrooms()->where('id', $classroom->id)->exists();
    }

    /**
     * Get the classrooms of which the teacher is a secondary teacher.
     *
     * @return BelongsToMany
     */
    public function secondaryClassrooms(): BelongsToMany
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
        return $this->secondaryClassrooms()->count() > 0;
    }

    /**
     * Indicate that if the teacher is a secondary teacher of the given classroom.
     * @param Classroom $classroom
     * @return bool
     */
    public function isSecondaryTeacherOfClassroom(Classroom $classroom): bool
    {
        return $this->secondaryClassrooms()->where('classroom_id', $classroom->id)->exists();
    }

    /**
     * Get distinct classrooms of which the teacher is either the owner or a secondary teacher.
     *
     * @return Collection<Classroom>
     */
    public function getOwnedAndSecondaryClassrooms(): Collection
    {
        $ownedClassrooms = $this->ownedClassrooms()->get();
        $secondaryClassrooms = $this->secondaryClassrooms()->get();

        return $ownedClassrooms->merge($secondaryClassrooms)->unique('id');
    }
}
