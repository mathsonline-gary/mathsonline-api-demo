<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends User
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
