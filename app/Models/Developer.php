<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Developer extends User
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
