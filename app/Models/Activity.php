<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    // Define activity type constants associated with authentication.
    public const TYPE_LOG_IN = 101;
    public const TYPE_LOG_OUT = 102;

    // Define activity type constants associated with teachers.
    public const TYPE_CREATE_TEACHER = 201;
    public const TYPE_UPDATE_TEACHER = 202;
    public const TYPE_DELETE_TEACHER = 203;

    // Define activity type constants associated with classrooms.
    public const TYPE_CREATE_CLASSROOM = 301;
    public const TYPE_UPDATE_CLASSROOM = 302;
    public const TYPE_DELETE_CLASSROOM = 303;
    public const TYPE_CREATE_CLASSROOM_GROUP = 304;

    // Define activity type constants associated with students.
    public const TYPE_CREATE_STUDENT = 401;
    public const TYPE_UPDATE_STUDENT = 402;
    public const TYPE_DELETE_STUDENT = 403;

    protected $table = 'activities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'data',
        'acted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'int',
        'data' => 'array',
        'acted_at' => 'datetime',
    ];

    public $timestamps = false;
}
