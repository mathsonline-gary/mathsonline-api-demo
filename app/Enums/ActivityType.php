<?php

namespace App\Enums;

enum ActivityType: int
{
    // Auth Enums
    case LOGGED_IN = 1;
    case LOGGED_OUT = 2;

    // Teacher Enums
    case CREATED_TEACHER = 3;
    case DELETED_TEACHER = 4;
    case UPDATED_TEACHER = 5;

    // Classroom Enums
    case CREATED_CLASSROOM = 6;
    case UPDATED_CLASSROOM = 7;
    case DELETED_CLASSROOM = 8;

    // Student Enums
    case CREATED_STUDENT = 9;
    case UPDATED_STUDENT = 10;
    case DELETED_STUDENT = 11;
}
