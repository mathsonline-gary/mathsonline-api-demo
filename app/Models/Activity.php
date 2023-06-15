<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use ReflectionClass;

class Activity extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'json',
        'created_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'action',
        'data',
        'acted_at',
    ];

    public $timestamps = false;

    // Define the available actions.
    public const ACTION_LOG_IN = 'log_in';
    public const ACTION_LOG_OUT = 'log_out';
    public const ACTION_CREATE_TEACHER = 'create_teacher';

    /**
     * Get the tokenable model that the activity belongs to.
     *
     * @return MorphTo
     */
    public function actionable(): MorphTo
    {
        return $this->morphTo('actionable');
    }

    /**
     * Get all available actions.
     *
     * @return array
     */
    public static function getActions(): array
    {
        $reflectionClass = new ReflectionClass(self::class);
        $constants = $reflectionClass->getConstants();
        $actions = [];

        foreach ($constants as $constantName => $constantValue) {
            if (str_starts_with($constantName, 'ACTION_')) {
                $actions[] = $constantValue;
            }
        }

        return $actions;
    }
}
