<?php

namespace App\Exceptions;

use Exception;

class DefaultClassroomGroupExistsException extends Exception
{
    protected $message = 'The default group of this classroom already exists.';

    protected $code = 409;
}
