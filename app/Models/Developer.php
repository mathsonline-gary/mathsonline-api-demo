<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Developer extends Model
{
    use HasFactory;

    protected $fillable = [
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
