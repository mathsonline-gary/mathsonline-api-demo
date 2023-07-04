<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class MaxClassroomGroupCountReachedException extends Exception
{
    protected $message = 'The max limit of the number of classroom groups has been reached.';

    protected $code = 422;

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
        ], $this->code);
    }
}
