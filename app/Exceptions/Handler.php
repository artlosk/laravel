<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('admin/*') && !($e instanceof \Illuminate\Auth\AuthenticationException)) {
                if ($e instanceof ValidationException) {
                    return redirect()->back()->withErrors($e->errors())->withInput();
                }
                return redirect()->back()->with('error', __('messages.operation_failed'));
            }
        });
    }
}
