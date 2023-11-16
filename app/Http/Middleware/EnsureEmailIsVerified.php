<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip this middleware if the user is a student or a teacher.
        if ($request->user() &&
            $request->user() instanceof MustVerifyEmail &&
            ($request->user()->isStudent() || $request->user()->isTeacher())) {

            return $next($request);
        }

        if (!$request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
                !$request->user()->hasVerifiedEmail())) {

            return response()->json(['message' => 'Your email address is not verified.'], 409);
        }

        return $next($request);
    }
}
