<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } 
        catch (TokenExpiredException $e) {
            // Jika token expired → redirect ke login
            return redirect('/login')->with('error', 'Session expired, silakan login kembali.');
        } 
        catch (TokenInvalidException $e) {
            return redirect('/login')->with('error', 'Token tidak valid, silakan login kembali.');
        } 
        catch (JWTException $e) {
            return redirect('/login')->with('error', 'Token tidak ditemukan, silakan login kembali.');
        }

        return $next($request);
    }
}
