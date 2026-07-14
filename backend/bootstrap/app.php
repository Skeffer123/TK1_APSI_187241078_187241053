<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role.admin' => \App\Http\Middleware\EnsureIsAdmin::class,
            'role.anggota' => \App\Http\Middleware\EnsureIsAnggota::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                // Biarkan Laravel menangani exception validasi & otentikasi secara bawaan
                if ($e instanceof \Illuminate\Validation\ValidationException ||
                    $e instanceof \Illuminate\Auth\AuthenticationException) {
                    return null;
                }

                // Tangani ModelNotFoundException (404)
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data tidak ditemukan.',
                        'data' => null
                    ], 404);
                }

                // Tangani HttpExceptionInterface (403, 404, 405, dll)
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'Akses ditolak atau halaman tidak ditemukan.',
                        'data' => null
                    ], $e->getStatusCode());
                }

                // Catat log error internal (500) ke storage/logs/laravel.log
                \Illuminate\Support\Facades\Log::error($e);

                $showDebug = config('app.debug', false);

                return response()->json([
                    'success' => false,
                    'message' => $showDebug ? $e->getMessage() : 'Terjadi kesalahan internal pada server.',
                    'data' => null,
                    ...($showDebug ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => array_slice(array_map(fn ($trace) => [
                            'file' => $trace['file'] ?? null,
                            'line' => $trace['line'] ?? null,
                            'function' => $trace['function'] ?? null,
                            'class' => $trace['class'] ?? null,
                        ], $e->getTrace()), 0, 10)
                    ] : [])
                ], 500);
            }
        });
    })->create();
