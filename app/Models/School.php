<?php

namespace App\Models;

use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\Tutor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'name',
        'type',
        'email',
        'phone',
        'fax',
        'address_line_1',
        'address_line_2',
        'address_city',
        'address_state',
        'address_postal_code',
        'address_country',
    ];

    /**
     * Get the school's tutors.
     *
     * @return HasMany
     */
    public function tutors(): HasMany
    {
        return $this->hasMany(Tutor::class);
    }

    /**
     * Get the school's teachers.
     *
     * @return HasMany
     */
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    /**
     * Get the school's instructors, can be tutors or teachers.
     *
     * @return HasMany
     */
    public function instructors(): HasMany
    {
        if ($this->type === 'homeschool') {
            return $this->tutors();
        }

        return $this->teachers();
    }

    /**
     * Get the school's students.
     *
     * @return HasMany
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
