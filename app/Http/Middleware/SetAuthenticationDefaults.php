<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
        $path = $request->getPathInfo();

        switch (true) {
            case Str::startsWith($path, '/tutors'):
            case Str::startsWith($path, '/api/tutors/v1'):
                Auth::setDefaultDriver('tutor');
                config(['sanctum.guard' => 'tutor']);
                Password::setDefaultDriver('tutors');

                break;

            case Str::startsWith($path, '/teachers'):
            case Str::startsWith($path, '/api/teachers/v1'):
                Auth::setDefaultDriver('teacher');
                config(['sanctum.guard' => 'teacher']);
                Password::setDefaultDriver('teachers');

                break;

            case Str::startsWith($path, '/students'):
            case Str::startsWith($path, '/api/students/v1'):
                Auth::setDefaultDriver('student');
                config(['sanctum.guard' => 'student']);
                Password::setDefaultDriver('students');

                break;

            case Str::startsWith($path, '/admins'):
            case Str::startsWith($path, '/api/admins/v1'):
                Auth::setDefaultDriver('admin');
                config(['sanctum.guard' => 'admin']);
                Password::setDefaultDriver('admins');

                break;

            case Str::startsWith($path, '/developers'):
            case Str::startsWith($path, '/api/developers/v1'):
                Auth::setDefaultDriver('developer');
                config(['sanctum.guard' => 'developer']);
                Password::setDefaultDriver('developers');

                break;
        }

        return $next($request);
    }
}
