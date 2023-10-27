<?php

namespace App\Enums;

enum UserType: int
{
    case TYPE_STUDENT = 1;
    case TYPE_TEACHER = 2;
    case TYPE_MEMBER = 3;
    case TYPE_ADMIN = 4;
    case TYPE_DEVELOPER = 5;
}
