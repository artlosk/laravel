<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
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
        // Log all exceptions
        $this->reportable(function (Throwable $e) {
            Log::error('Exception: ' . $e->getMessage(), ['exception' => $e]);
        });

        // Custom rendering for admin routes
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('admin/*') && !($e instanceof \Illuminate\Auth\AuthenticationException)) {
                // Allow standard handling for validation exceptions
                if ($e instanceof ValidationException) {
                    Log::debug('Handling ValidationException', [
                        'errors' => $e->errors(),
                        'input' => $request->input(),
                        'session_before' => session()->all(),
                        'flash_before' => session()->get('_flash', []),
                        'back' => $request->headers->get('referer', url()->previous()),
                    ]);
                    $redirect = redirect()->back()->withErrors($e->errors())->withInput();
                    Log::debug('After withErrors', [
                        'session_after' => session()->all(),
                        'flash_after' => session()->get('_flash', []),
                        'errors_in_session' => session()->get('errors', 'No errors in session'),
                    ]);
                    return $redirect;
                }
                // Handle other exceptions with a generic error message
                return redirect()->back()->with('error', __('messages.operation_failed'));
            }
        });
    }
}
