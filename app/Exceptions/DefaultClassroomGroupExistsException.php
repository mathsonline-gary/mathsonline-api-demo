<?php

namespace App\Exceptions;

use Exception;

class DefaultClassroomGroupExistsException extends Exception
{
    protected $message = 'The default classroom group already exists.';

    protected $code = 409;
}
