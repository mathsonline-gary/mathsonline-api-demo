<?php

namespace App\Exceptions;

use Exception;

class MaxClassroomGroupCountReachedException extends Exception
{
    protected $message = 'The max limit of the number of classroom groups has been reached.';

    protected $code = 409;
}
