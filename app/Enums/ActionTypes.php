<?php

namespace App\Enums;

enum ActionTypes: string
{
    /*
    |--------------------------------------------------------------------------
    | Authentication Enums
    |--------------------------------------------------------------------------
    |
    | Enums relative to authentication.
    |
    */

    case LOG_IN = 'log in';
    case LOG_OUT = 'log out';

    /*
    |--------------------------------------------------------------------------
    | Teacher Enums
    |--------------------------------------------------------------------------
    |
    | Enums relative to teacher module.
    |
    */
    case CREATE_TEACHER = 'create teacher';
    case DELETE_TEACHER = 'delete teacher';
    case UPDATE_TEACHER = 'update teacher';
}
