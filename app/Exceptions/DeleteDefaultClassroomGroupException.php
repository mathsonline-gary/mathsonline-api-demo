<?php

namespace App\Exceptions;

use Exception;

class DeleteDefaultClassroomGroupException extends Exception
{
    protected $message = 'You are not allowed to delete the default classroom group.';

    protected $code = 409;
}
