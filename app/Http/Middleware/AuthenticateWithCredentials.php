<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateWithCredentials
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Проверка Bearer Token (Sanctum)
        if ($request->bearerToken() && Auth::guard('sanctum')->check()) {
            return $next($request);
        }

        // 2. Проверка Basic Auth
        if ($request->header('Authorization') && str_starts_with($request->header('Authorization'), 'Basic ')) {
            $credentials = base64_decode(substr($request->header('Authorization'), 6));
            [$email, $password] = explode(':', $credentials, 2);

            if (Auth::once(['email' => $email, 'password' => $password])) {
                return $next($request);
            }
        }

        // 3. Проверка через кастомные заголовки (как ранее)
        if ($request->hasHeader('X-Email') && $request->hasHeader('X-Password')) {
            if (Auth::once([
                'email' => $request->header('X-Email'),
                'password' => $request->header('X-Password')
            ])) {
                return $next($request);
            }
        }

        // 4. Если ни один метод не сработал
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
