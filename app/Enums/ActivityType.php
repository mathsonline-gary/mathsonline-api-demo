<?php

namespace App\Enums;

enum ActivityType: string
{
    // Auth Enums
    case LOGGED_IN = '101';
    case LOGGED_OUT = '102';

    // Teacher Enums
    case CREATED_TEACHER = '201';
    case DELETED_TEACHER = '202';
    case UPDATED_TEACHER = '203';

    // Classroom Enums
    case CREATED_CLASSROOM = '301';
    case UPDATED_CLASSROOM = '302';
    case DELETED_CLASSROOM = '303';
    case CREATED_CLASSROOM_GROUP = '304';

    // Student Enums
    case CREATED_STUDENT = '401';
    case UPDATED_STUDENT = '402';
    case DELETED_STUDENT = '403';
}
