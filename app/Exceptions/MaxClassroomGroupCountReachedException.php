<?php

namespace App\Exceptions;

use Exception;

class MaxClassroomGroupCountReachedException extends Exception
{
    protected $message = 'Maximum number of groups reached.';

    protected $code = 422;
}
