<?php

namespace App\Enums;

enum ActivityTypes: string
{
    /*
    |--------------------------------------------------------------------------
    | Authentication Enums
    |--------------------------------------------------------------------------
    |
    | Enums relative to authentication.
    |
    */

    case LOGGED_IN = 'logged in';
    case LOGGED_OUT = 'logged out';

    /*
    |--------------------------------------------------------------------------
    | Teacher Enums
    |--------------------------------------------------------------------------
    |
    | Enums relative to teacher module.
    |
    */
    case CREATED_TEACHER = 'created teacher';
    case DELETED_TEACHER = 'deleted teacher';
    case UPDATED_TEACHER = 'updated teacher';
}