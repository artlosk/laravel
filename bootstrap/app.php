<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware for web routes
        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class, // Encrypt cookies
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, // Add cookies to response
            \Illuminate\Session\Middleware\StartSession::class, // Start session
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, // Sanctum stateful requests
            \Illuminate\View\Middleware\ShareErrorsFromSession::class, // Share validation errors
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, // CSRF protection
            \Illuminate\Routing\Middleware\SubstituteBindings::class, // Route model binding
        ]);

        // Middleware for API routes
        $middleware->api(append: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'auth.credentials' => \App\Http\Middleware\AuthenticateWithCredentials::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'role' => RoleMiddleware::class, // Spatie role middleware
            'permission' => PermissionMiddleware::class, // Spatie permission middleware
            'check.permission' => \App\Http\Middleware\CheckPermissionForApi::class,
        ]);
    })
    ->withProviders([
        \App\Providers\RateLimitServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
