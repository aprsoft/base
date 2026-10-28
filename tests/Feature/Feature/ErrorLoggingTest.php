<?php

use App\Models\ErrorLog;
use App\Services\ErrorLog\ErrorLogger;
use Illuminate\Auth\Access\AuthorizationException;

// tests/Pest.php aplica RefreshDatabase a tests/Feature.
test('un error reportado se guarda una vez sin incluir su mensaje privado', function () {
    $exception = new RuntimeException('password=ejemplo-sensible');

    report($exception);
    report($exception);

    $records = ErrorLog::query()
        ->where('exception', RuntimeException::class)
        ->get();

    expect($records)->toHaveCount(1)
        ->and($records->first()->message)->not->toContain('ejemplo-sensible');
});

test('las llamadas directas conservan metadatos técnicos permitidos', function () {
    app(ErrorLogger::class)->report(new RuntimeException('prueba de error'), [
        'operation' => 'user.create',
        'error_type' => 'unexpected',
        'password' => 'no-guardar',
    ]);

    $record = ErrorLog::query()->firstOrFail();

    expect($record->errors)->toBe([
        'operation' => 'user.create',
        'error_type' => 'unexpected',
    ]);
});

test('las denegaciones de autorización no se registran como errores internos', function () {
    report(new AuthorizationException('Acceso denegado'));

    expect(ErrorLog::query()->count())->toBe(0);
});
