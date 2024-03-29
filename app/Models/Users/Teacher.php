<?php

namespace App\Models\Users;

use App\Concerns\BelongsToUser;
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
        BelongsToUser;

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
        return $this->id === $classroom->owner_id;
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
     *
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
        $ownedClassrooms = $this->ownedClassrooms;
        $secondaryClassrooms = $this->secondaryClassrooms;

        return $ownedClassrooms->merge($secondaryClassrooms)->unique('id');
    }

    /**
     * Indicate that if the teacher manages the given classroom.
     * If the teacher is an admin, then the classroom must be from the same school as the teacher.
     * If the teacher is a non-admin, then the classroom must be from the same school and be owned or be a secondary classroom of the teacher.
     *
     * @param Classroom $classroom
     * @return bool
     */
    public function canManageClassroom(Classroom $classroom): bool
    {
        if ($this->isAdmin()) {
            return $this->school_id === $classroom->school_id;
        } else {
            return $this->school_id === $classroom->school_id &&
                ($this->isOwnerOfClassroom($classroom) || $this->isSecondaryTeacherOfClassroom($classroom));
        }
    }

    /**
     * Get classrooms that are managed by the teacher:
     * If the teacher is an admin, then all classrooms from the same school are returned.
     * If the teacher is a non-admin, then only classrooms that they own or are secondary teachers of are returned.
     *
     * @return Collection<Classroom>
     */
    public function getManagedClassrooms(): Collection
    {
        if ($this->isAdmin()) {
            return $this->school->classrooms;
        }

        return $this->getOwnedAndSecondaryClassrooms();
    }

    /**
     * Indicate that if the teacher manages the given student.
     * If the teacher is an admin, then the student must be from the same school.
     * If the teacher is a non-admin, then the student must be from the same school and from a classroom that they manage.
     *
     * @param Student $student
     * @return bool
     */
    public function canManageStudent(Student $student): bool
    {
        if ($this->isAdmin()) {
            return $this->school_id === $student->school_id;
        } else {
            return $this->school_id === $student->school_id &&
                $student->classroomGroups()
                    ->whereIn('classroom_id',
                        $this->getOwnedAndSecondaryClassrooms()
                            ->pluck('id')
                            ->toArray())
                    ->exists();
        }
    }
}
