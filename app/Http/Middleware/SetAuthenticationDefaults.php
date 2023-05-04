<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class SetAuthenticationDefaults
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $referer = $request->header('Referer');

        if ($referer) {
            $path = parse_url($referer, PHP_URL_PATH);
            $segments = explode('/', trim($path, '/'));
            $role = $segments[0] ?? null;

            $driver = match ($role) {
                'tutor' => 'tutors',
                'teacher' => 'teachers',
                'student' => 'students',
                'admin' => 'admins',
                'developer' => 'developers',
                default => null,
            };

            if ($driver !== null) {
                // Set default auth guard provider
                Auth::setDefaultDriver($driver);

                // Set default password resetting driver
                Password::setDefaultDriver($driver);
            }
        }

        return $next($request);
    }
}
