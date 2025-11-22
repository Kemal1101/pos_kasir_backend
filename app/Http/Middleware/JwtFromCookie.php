<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Crypt;

class JwtFromCookie
{
    public function handle($request, Closure $next)
    {
        // Ambil token dari cookie
        $token = $request->cookie('jwt_token');

        if ($token) {
            // Jika belum terdekripsi (API group sebelumnya tidak punya EncryptCookies) coba decrypt manual
            if (!str_contains($token, '.')) {
                try {
                    $decrypted = Crypt::decryptString($token);
                    $token = $decrypted ?: $token;
                } catch (\Throwable $e) {
                    // Biarkan token apa adanya; jika tetap tidak valid akan dibersihkan
                }
            }
            // Validasi pola dasar JWT
            if (preg_match('/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/', $token)) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            } else {
                cookie()->queue(cookie()->forget('jwt_token'));
            }
        }

        return $next($request);
    }
}
