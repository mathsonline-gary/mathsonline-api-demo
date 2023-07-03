<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class MaxClassroomGroupCountReachedException extends Exception
{
    protected $message = 'Maximum number of groups reached.';

    protected $code = 422;

    public function render(): Response
    {
        return response($this->message, $this->code);
    }
}
