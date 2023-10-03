<?php

namespace App\Models\Users;

use App\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory,
        BelongsToUser;

    protected $fillable = [
        'market_id',
        'username',
        'email',
        'first_name',
        'last_name',
    ];
}
