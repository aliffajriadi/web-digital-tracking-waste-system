<?php

use Illuminate\Database\QueryException;
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
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'pic.active' => \App\Http\Middleware\EnsureActivePic::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Data master yang masih dipakai transaksi tidak bisa dihapus (foreign key).
        // Tampilkan pesan yang bisa dipahami alih-alih halaman error SQL.
        $exceptions->render(function (QueryException $e, Request $request) {
            $isForeignKey = str_contains(strtolower($e->getMessage()), 'foreign key');
            if (!$isForeignKey) {
                return null;
            }

            $message = 'Data tidak dapat dihapus/diubah karena masih digunakan oleh data lain.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 409);
            }

            return back()->with('error', $message);
        });
    })->create();
