<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException; // Opsional: Untuk menangani error validasi

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof NotFoundHttpException) {
                return response()->json(['message' => 'Resource not found.'], 404);
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            // Untuk error lainnya, kembalikan JSON error generik
            $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
            if ($statusCode < 100 || $statusCode >= 600) { // Pastikan status code valid
                $statusCode = 500;
            }

            return response()->json([
                'message' => $exception->getMessage() ?: 'An unexpected error occurred.',
                'status' => 'error',
                // 'trace' => $exception->getTraceAsString(), // Aktifkan hanya untuk debugging, matikan di produksi
            ], $statusCode);
        }

        return parent::render($request, $exception);
    }
}