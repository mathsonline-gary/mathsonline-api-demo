<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'username',
        'email',
        'first_name',
        'last_name',
        'password'
    ];

    protected $hidden = [
        'password',
    ];
}
