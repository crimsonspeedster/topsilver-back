<?php
namespace App\Http\Middleware;

use Closure;

class InjectBearerTokenFromCookie
{
    public function handle($request, Closure $next)
    {
        $token = urldecode(
            $request->cookie('access_token', '')
        );

        if ($token && !$request->bearerToken()) {
            $request->headers->set(
                'Authorization',
                'Bearer ' . $token
            );
        }

        return $next($request);
    }
}
