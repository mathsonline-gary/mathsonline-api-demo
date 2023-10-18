<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    protected $fillable = [
        'market_id',
        'name',
        'order',
    ];

    public $timestamps = false;
}
