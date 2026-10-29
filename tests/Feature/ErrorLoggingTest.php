<?php

use App\Livewire\User\UserEdit;
use App\Models\ErrorLog;
use App\Models\User;
use App\Services\ErrorLog\ErrorLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('una excepcion reportada dos veces se persiste solo una vez sin exponer su mensaje', function () {
    $exception = new RuntimeException('password=ejemplo-sensible');

    report($exception);
    report($exception);

    $records = ErrorLog::query()
        ->where('exception', RuntimeException::class)
        ->get();

    expect($records)->toHaveCount(1)
        ->and($records->first()->message)->toBe('Error interno (RuntimeException)')
        ->and($records->first()->message)->not->toContain('ejemplo-sensible');
});

test('persist almacena solo los metadatos permitidos', function () {
    app(ErrorLogger::class)->persist(new RuntimeException('prueba de error'), [
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

test('una denegacion de autorizacion no se persiste como fallo interno', function () {
    report(new AuthorizationException('Acceso denegado'));

    expect(ErrorLog::query()->count())->toBe(0);
});

test('UserEdit muestra alerta y no guarda cambios cuando falla la actualizacion', function () {
    User::factory()->create(); // Cuenta principal ID 1.
    $superAdmin = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->assignRole($superAdmin);
    $target = User::factory()->create(['name' => 'Nombre Original']);
    $target->assignRole($admin);

    User::saving(function (User $user) use ($target): void {
        if ($user->is($target)) {
            throw new RuntimeException('FALLO_CONTROLADO_USER_EDIT');
        }
    });

    Livewire::actingAs($actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('name', 'Nombre Nuevo')
        ->call('save', $target->id)
        ->assertDispatched(
            'app-alert',
            type: 'error',
            message: 'No fue posible actualizar el usuario. Intenta nuevamente.'
        )
        ->assertNoRedirect();

    dump(["errores_registrados" => \App\Models\ErrorLog::query()->get(["exception", "message"])->toArray()]);
    expect($target->fresh()->name)->toBe('Nombre Original')
        ->and(ErrorLog::query()->where('exception', RuntimeException::class)->count())->toBe(1);
});

test('UserEdit muestra alerta y no suspende cuando falla la operacion', function () {
    User::factory()->create(); // Cuenta principal ID 1.
    $superAdmin = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->assignRole($superAdmin);
    $target = User::factory()->create();

    User::deleting(function (User $user) use ($target): void {
        if ($user->is($target)) {
            throw new RuntimeException('FALLO_CONTROLADO_SUSPENDER');
        }
    });

    Livewire::actingAs($actor)
        ->test(UserEdit::class, ['user' => $target])
        ->call('suspend', $target->id)
        ->assertDispatched(
            'app-alert',
            type: 'error',
            message: 'No fue posible suspender al usuario. Intenta nuevamente.'
        )
        ->assertNoRedirect();

    expect(User::withTrashed()->findOrFail($target->id)->trashed())->toBeFalse()
        ->and(ErrorLog::query()->where('exception', RuntimeException::class)->count())->toBe(1);
});

test('UserEdit muestra alerta y conserva suspension cuando falla restauracion', function () {
    User::factory()->create(); // Cuenta principal ID 1.
    $superAdmin = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->assignRole($superAdmin);
    $target = User::factory()->create();
    $target->delete();

    User::restoring(function (User $user) use ($target): void {
        if ($user->is($target)) {
            throw new RuntimeException('FALLO_CONTROLADO_RESTAURAR');
        }
    });

    Livewire::actingAs($actor)
        ->test(UserEdit::class, ['user' => $target])
        ->call('restore', $target->id)
        ->assertDispatched(
            'app-alert',
            type: 'error',
            message: 'No fue posible restaurar al usuario. Intenta nuevamente.'
        )
        ->assertNoRedirect();

    expect(User::withTrashed()->findOrFail($target->id)->trashed())->toBeTrue()
        ->and(ErrorLog::query()->where('exception', RuntimeException::class)->count())->toBe(1);
});
