<?php

namespace App\Http\Middleware;

use Closure;

class JwtFromCookie
{
    public function handle($request, Closure $next)
    {
        // Ambil token dari cookie
        $token = $request->cookie('jwt_token');

        if ($token) {
            // Tambahkan ke Authorization header
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
