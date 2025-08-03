<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateWithCredentials
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken() && Auth::guard('sanctum')->check()) {
            return $next($request);
        }

        if ($request->header('Authorization') && str_starts_with($request->header('Authorization'), 'Basic ')) {
            $credentials = base64_decode(substr($request->header('Authorization'), 6));
            [$email, $password] = explode(':', $credentials, 2);

            if (Auth::once(['email' => $email, 'password' => $password])) {
                return $next($request);
            }
        }

        if ($request->hasHeader('X-Email') && $request->hasHeader('X-Password')) {
            if (Auth::once([
                'email' => $request->header('X-Email'),
                'password' => $request->header('X-Password')
            ])) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
