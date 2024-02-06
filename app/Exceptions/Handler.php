<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException as StripeApiErrorException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Handle Stripe API error exceptions.
        $this->reportable(function (StripeApiErrorException $e) {
            // Log the error.
            Log::channel('stripe')->error($e->getMessage(), [
                'code' => $e->getCode(),
                'http_status' => $e->getHttpStatus(),
                'json_body' => $e->getJsonBody(),
                'exception' => $e,
            ]);

            // Send a notification to the developers.
            // TODO
        });
    }
}
