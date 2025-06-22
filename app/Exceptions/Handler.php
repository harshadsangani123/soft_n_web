<?php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    private function handleApiException($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Authentication required'
            ], 401);
        }

        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $exception->errors()
            ], 422);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Resource not found.',
                'error' => 'The requested resource was not found'
            ], 404);
        }

        if ($exception instanceof AccessDeniedHttpException) {
            return response()->json([
                'message' => 'Access denied.',
                'error' => 'You do not have permission to access this resource'
            ], 403);
        }

        // General server error
        if (config('app.debug')) {
            return response()->json([
                'message' => 'Server error.',
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ], 500);
        }

        return response()->json([
            'message' => 'Server error.',
            'error' => 'An unexpected error occurred'
        ], 500);
    }
}