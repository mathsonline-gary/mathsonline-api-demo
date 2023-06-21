<?php

namespace App\Models;

use App\Enums\ActionTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Action extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => ActionTypes::class,
        'data' => 'json',
        'acted_at' => 'datetime',
    ];

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

    public $timestamps = false;

    /**
     * Get the actionable model that the action belongs to.
     *
     * @return MorphTo
     */
    public function actionable(): MorphTo
    {
        return $this->morphTo('actionable');
    }
}
