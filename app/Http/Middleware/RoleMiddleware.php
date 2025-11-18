<?php

namespace App\Http\Middleware;

use App\Utils\Response;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            // Ambil user dari JWT
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return Response::unauthorized('Token tidak valid');
            }

            // Ambil role_name dari relasi role
            $roleName = $user->role->name ?? null;

            // Validasi role
            if (!in_array($roleName, $roles)) {
                return Response::unauthorized('Anda tidak memiliki akses ke resource ini');
            }
        } catch (Exception $e) {

            return Response::error(
                $e,
                'Token tidak valid atau telah kadaluarsa',
                401
            );
        }

        return $next($request);
    }
}
