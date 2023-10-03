<?php

namespace App\Models;

use App\Models\Users\Member;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'name',
        'type_id',
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

    protected $casts = [
        'type_id' => 'int',
    ];

    public const TYPE_TRADITIONAL_SCHOOL = 1;
    public const TYPE_HOMESCHOOL = 2;

    /**
     * Get the owner of the school (homeschool).
     *
     * @return HasOne
     */
    public function owner(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    /**
     * Get the teachers of the school (traditional school).
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
        return $query->where('type_id', School::TYPE_TRADITIONAL_SCHOOL);
    }

    /**
     * Scope a query to only include homeschools.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeHomeschools(Builder $query): Builder
    {
        return $query->where('type_id', School::TYPE_HOMESCHOOL);
    }
}
