<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse();
        }

        // If the user is a student, check if they have an active subscription.
        if (
            $user->isStudent()
            && !$user->asStudent()->school->hasActiveSubscription()
        ) {
            return $this->errorResponse();
        }

        // If the user is a teacher, check if they have an active subscription.
        if (
            $user->isTeacher()
            && !$user->asTeacher()->school->hasActiveSubscription()
        ) {
            return $this->errorResponse();
        }

        // If the user is a member, check if they have an active subscription.
        if (
            $user->isMember()
            && !$user->asMember()->school->hasActiveSubscription()
        ) {
            return $this->errorResponse();
        }

        return $next($request);
    }

    protected function errorResponse(): JsonResponse
    {
        return response()->json(['message' => 'You do not have an active subscription.'], 409);
    }
}
