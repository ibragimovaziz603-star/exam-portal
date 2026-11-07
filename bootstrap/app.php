<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. Proxy sozlamasi (IP to'g'ri aniqlanishi uchun)
        
        $middleware->trustProxies(at: '*');

        // 2. Admin uchun alias
        $middleware->alias(['admin' => \App\Http\Middleware\AdminOnly::class]);

        // 3. Web guruhiga middleware'larni qo'shish
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\TrackVisits::class,
            \App\Http\Middleware\PageEffects::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();