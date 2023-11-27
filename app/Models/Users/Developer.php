<?php

namespace App\Models\Users;

use App\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Developer extends Model
{
    use HasFactory,
        BelongsToUser;

    protected $table = 'developers';

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];
}
