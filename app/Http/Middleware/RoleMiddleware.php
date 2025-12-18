<?php

namespace App\Http\Middleware;

use App\Utils\Response;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            // Autentikasi token
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->redirectOrJson($request, 'Token tidak valid atau tidak ditemukan');
            }

            // Ambil role user
            $roleName = $user->role->name ?? null;

            // Cek apakah role diizinkan
            if (!in_array($roleName, $roles)) {
                return $this->redirectOrJson($request, 'Anda tidak memiliki akses ke halaman ini', null, 403);
            }
        } catch (TokenExpiredException $e) {
            return $this->redirectOrJson($request, 'Token telah kadaluarsa', $e, 401);
        } catch (TokenInvalidException $e) {
            return $this->redirectOrJson($request, 'Token tidak valid', $e, 401);
        } catch (JWTException | Exception $e) {
            return $this->redirectOrJson($request, 'Token tidak valid atau sudah kadaluarsa', $e, 401);
        }

        return $next($request);
    }

    // Helper untuk API & Web
    private function redirectOrJson(Request $request, string $message, Exception $e = null, int $code = 401)
    {
        // ⛳ API selalu return JSON meskipun Accept: */*
        if ($request->expectsJson() || $request->is('api/*')) {

            return Response::error(
                $e,
                $message,
                $code
            );
        }

        // ⛳ Jika dari browser, redirect ke login
        return redirect('/login')->with('error', $message);
    }
}
