<?php

namespace App\Models\Users;

use App\Concerns\HasCredentials;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Developer extends Model
{
    use HasFactory,
        HasCredentials;

    protected $fillable = [
        'username',
        'email',
        'first_name',
        'last_name',
    ];
}
