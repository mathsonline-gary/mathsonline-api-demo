<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class DefaultClassroomGroupExistsException extends Exception
{
    protected $message = 'The default classroom group already exists.';

    protected $code = 409;

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
        ], $this->code);
    }
}
