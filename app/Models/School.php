<?php

namespace App\Models;

use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\Tutor;
use Illuminate\Database\Eloquent\Builder;
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

    public const TRADITIONAL_SCHOOL = 'traditional school';
    public const HOMESCHOOL = 'homeschool';

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
     * Get the school's students.
     *
     * @return HasMany
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Scope a query to only include traditional schools.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeTraditionalSchools(Builder $query): Builder
    {
        return $query->where('type', School::TRADITIONAL_SCHOOL);
    }

    /**
     * Scope a query to only include homeschools.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeHomeschools(Builder $query): Builder
    {
        return $query->where('type', School::HOMESCHOOL);
    }
}
