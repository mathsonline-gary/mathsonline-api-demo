<?php

namespace App\Models;

use App\Enums\ActivityTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => ActivityTypes::class,
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
     * Get the actable model that the activity belongs to.
     *
     * @return MorphTo
     */
    public function actable(): MorphTo
    {
        return $this->morphTo('actable');
    }
}
