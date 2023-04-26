<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetDefaultAuthGuard
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
            $parsedUrl = parse_url($referer);
            $subdomain = explode('.', $parsedUrl['host'])[0];

            switch ($subdomain) {
                case 'tutor':
                    Auth::setDefaultDriver('tutor');
                    break;

                case 'teacher':
                    Auth::setDefaultDriver('teacher');
                    break;

                case 'student':
                    Auth::setDefaultDriver('student');
                    break;

                case 'admin':
                    Auth::setDefaultDriver('admin');
                    break;

                case 'dev':
                    Auth::setDefaultDriver('developer');
                    break;
            }
        }

        return $next($request);
    }
}
