<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionForApi
{
    public function handle($request, Closure $next, $permission)
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "Forbidden: {$permission}");
        }

        return $next($request);
    }
}
