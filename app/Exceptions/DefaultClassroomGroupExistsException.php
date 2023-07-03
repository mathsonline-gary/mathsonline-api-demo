<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class DefaultClassroomGroupExistsException extends Exception
{
    protected $message = 'The default group of this classroom already exists.';

    protected $code = 409;

    public function render(): Response
    {
        return response($this->message, $this->code);
    }
}
