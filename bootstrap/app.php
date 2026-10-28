<?php

use App\Services\ErrorLog\ErrorLogger;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Evita registrar varias veces la misma instancia
        // de una excepción.
        $exceptions->dontReportDuplicates();

        // Registro automático de excepciones reportables
        // en la tabla error_logs mediante ErrorLogger.
        $exceptions->report(function (Throwable $exception): void {
            app(ErrorLogger::class)->persist($exception);
        });

        // Laravel continúa registrando la excepción
        // mediante sus canales de logging configurados.

    })->create();
