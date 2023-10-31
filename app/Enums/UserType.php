<?php

namespace App\Enums;

enum UserType: int
{
    case STUDENT = 1;
    case TEACHER = 2;
    case MEMBER = 3;
    case ADMIN = 4;
    case DEVELOPER = 5;
}
