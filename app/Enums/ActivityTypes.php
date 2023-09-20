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

    /*
    |--------------------------------------------------------------------------
    | Classroom Enums
    |--------------------------------------------------------------------------
    |
    | Enums relative to classroom module.
    |
    */
    case CREATED_CLASSROOM = 'created classroom';
    case UPDATED_CLASSROOM = 'updated classroom';
    case DELETED_CLASSROOM = 'deleted classroom';

    /*
    |--------------------------------------------------------------------------
    | Student Enums
    |--------------------------------------------------------------------------
    |
    | Enums relative to student module.
    |
    */
    case CREATED_STUDENT = 'created student';
    case UPDATED_STUDENT = 'updated student';
    case DELETED_STUDENT = 'deleted student';
}
