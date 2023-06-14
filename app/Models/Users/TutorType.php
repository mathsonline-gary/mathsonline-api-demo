<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class TutorType extends Model
{
    public const PRIMARY_PARENT = 'primary parent';

    public const SECONDARY_PARENT = 'secondary parent';

    public $timestamps = false;
}
