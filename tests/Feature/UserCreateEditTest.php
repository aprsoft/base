<?php

declare(strict_types=1);

use App\Livewire\User\UserCreate;
use App\Livewire\User\UserEdit;
use App\Models\ErrorLog;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Pruebas de integración Pest 4 / Livewire 4.
 * tests/Pest.php aplica RefreshDatabase a los tests de Feature.
 * IMPORTANTE: ejecutar EXCLUSIVAMENTE contra una BD de pruebas desechable.
 * Para MySQL, utilizar DB_DATABASE=base_testing (nunca DB_DATABASE=base).
 */

// Compatible con UserCreate actual (propiedades simples) o la variante UserForm.
function baseCreateField(string $field): string
{
    return property_exists(UserCreate::class, 'form') ? 'form.'.$field : $field;
}

beforeEach(function (): void {
    // Prevenir que la suite prosiga si su base no es la de pruebas.
    // RefreshDatabase ocurre antes de beforeEach: utilizar SIEMPRE el runner
    // de comprobación previa entregado junto a este test.
    expect(config('database.default'))->toBe('mysql');
    expect(config('database.connections.mysql.database'))->toBe('base_testing');
    expect(app()->environment('testing'))->toBeTrue();

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    // Id explícito: en MySQL los AUTO_INCREMENT no retroceden con rollback.
    $this->primary = User::factory()->create(['id' => 1]);
    $this->superAdminRole = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $this->adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $this->operatorRole = Role::create(['name' => 'Operator', 'guard_name' => 'web']);

    $this->actor = User::factory()->create();
    $this->actor->assignRole($this->superAdminRole);
});

function baseUserTarget(Role $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// -------------------------------------------------------------------------
// UserCreate: autorizaciones, validación, persistencia, notificaciones.
// -------------------------------------------------------------------------

test('UserCreate confirma sin insertar registros', function (): void {
    $initial = User::count();

    Livewire::actingAs($this->actor)
        ->test(UserCreate::class)
        ->set(baseCreateField('name'), ' Persona Nueva ')
        ->set(baseCreateField('email'), ' NUEVA@example.test ')
        ->set(baseCreateField('roleIds'), [$this->adminRole->id])
        ->call('confirmSave')
        ->assertHasNoErrors()
        ->assertDispatched('open-confirm-modal', confirmEvent: 'user-create-save-confirmed');

    expect(User::count())->toBe($initial);
});

test('UserCreate crea usuario, normaliza datos, asigna roles y solicita enlace', function (): void {
    Password::shouldReceive('sendResetLink')
        ->once()
        ->with(['email' => 'nuevo@example.test'])
        ->andReturn(Password::RESET_LINK_SENT);

    Livewire::actingAs($this->actor)
        ->test(UserCreate::class)
        ->set(baseCreateField('name'), '  Usuario Nuevo  ')
        ->set(baseCreateField('email'), '  NUEVO@EXAMPLE.TEST  ')
        ->set(baseCreateField('roleIds'), [$this->adminRole->id])
        ->call('confirmSave')
        ->assertHasNoErrors()
        ->call('save', 0)
        ->assertHasNoErrors()
        ->assertRedirectToRoute('users.index');

    $created = User::where('email', 'nuevo@example.test')->firstOrFail();

    expect($created->name)->toBe('Usuario Nuevo')
        ->and($created->hasRole('Admin', 'web'))->toBeTrue()
        ->and($created->password)->not->toBeEmpty();
});

test('UserCreate rechaza nombre, email y roles vacíos', function (): void {
    Livewire::actingAs($this->actor)
        ->test(UserCreate::class)
        ->call('confirmSave')
        ->assertHasErrors([
            baseCreateField('name') => 'required',
            baseCreateField('email') => 'required',
            baseCreateField('roleIds') => 'required',
        ]);
});

test('UserCreate rechaza un correo ya existente', function (): void {
    $existing = User::factory()->create(['email' => 'existente@example.test']);

    Livewire::actingAs($this->actor)
        ->test(UserCreate::class)
        ->set(baseCreateField('name'), 'Otro Usuario')
        ->set(baseCreateField('email'), $existing->email)
        ->set(baseCreateField('roleIds'), [$this->adminRole->id])
        ->call('confirmSave')
        ->assertHasErrors([baseCreateField('email') => 'unique']);
});

test('UserCreate exige permiso users.create', function (): void {
    $unprivileged = User::factory()->create();

    Livewire::actingAs($unprivileged)
        ->test(UserCreate::class)
        ->assertForbidden();
});

test('UserCreate impide asignar SuperAdmin sin autorización', function (): void {
    Permission::findOrCreate('users.create', 'web');
    $limited = User::factory()->create();
    $limited->givePermissionTo('users.create');

    Livewire::actingAs($limited)
        ->test(UserCreate::class)
        ->set(baseCreateField('name'), 'Usuario sin privilegios')
        ->set(baseCreateField('email'), 'limitado@example.test')
        ->set(baseCreateField('roleIds'), [$this->superAdminRole->id])
        ->call('confirmSave')
        ->assertHasErrors([baseCreateField('roleIds.0') => 'in']);
});

test('UserCreate impide seleccionar roles repetidos', function (): void {
    Livewire::actingAs($this->actor)
        ->test(UserCreate::class)
        ->set(baseCreateField('name'), 'Usuario repetido')
        ->set(baseCreateField('email'), 'repetido@example.test')
        ->set(baseCreateField('roleIds'), [$this->adminRole->id, $this->adminRole->id])
        ->call('confirmSave')
        ->assertHasErrors([baseCreateField('roleIds.0') => 'distinct']);
});

test('UserCreate permite quitar un rol seleccionado mediante confirmación', function (): void {
    Livewire::actingAs($this->actor)
        ->test(UserCreate::class)
        ->set(baseCreateField('roleIds'), [$this->adminRole->id, $this->operatorRole->id])
        ->call('confirmRemoveRole', $this->adminRole->id)
        ->assertDispatched('open-confirm-modal', confirmEvent: 'user-create-remove-role-confirmed')
        ->call('removeRole', $this->adminRole->id)
        ->assertSet(baseCreateField('roleIds'), [$this->operatorRole->id]);
});

test('UserCreate revierte la inserción si falla la creación', function (): void {
    User::created(function (User $user): void {
        if ($user->email === 'fallocreacion@example.test') {
            throw new RuntimeException('FALLO_CONTROLADO_CREACION');
        }
    });

    Livewire::actingAs($this->actor)
        ->test(UserCreate::class)
        ->set(baseCreateField('name'), 'Fallo de prueba')
        ->set(baseCreateField('email'), 'fallocreacion@example.test')
        ->set(baseCreateField('roleIds'), [$this->adminRole->id])
        ->call('save', 0)
        ->assertHasErrors('form')
        ->assertNoRedirect();

    expect(User::where('email', 'fallocreacion@example.test')->exists())->toBeFalse()
        ->and(ErrorLog::where('exception', RuntimeException::class)->exists())->toBeTrue();
});

test('UserCreate conserva usuario y roles si falla el envío del enlace', function (): void {
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andThrow(new RuntimeException('SMTP_NO_DISPONIBLE'));

    Livewire::actingAs($this->actor)
        ->test(UserCreate::class)
        ->set(baseCreateField('name'), 'Usuario Persistente')
        ->set(baseCreateField('email'), 'persistente@example.test')
        ->set(baseCreateField('roleIds'), [$this->adminRole->id])
        ->call('save', 0)
        ->assertRedirectToRoute('users.index');

    $user = User::where('email', 'persistente@example.test')->firstOrFail();
    expect($user->hasRole('Admin', 'web'))->toBeTrue()
        ->and(session()->has('warning'))->toBeTrue();
});

test('UserCreate informa si el proveedor no acepta el enlace', function (): void {
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andReturn(Password::INVALID_USER);

    Livewire::actingAs($this->actor)
        ->test(UserCreate::class)
        ->set(baseCreateField('name'), 'Usuario Aviso')
        ->set(baseCreateField('email'), 'aviso@example.test')
        ->set(baseCreateField('roleIds'), [$this->adminRole->id])
        ->call('save', 0)
        ->assertRedirectToRoute('users.index');

    expect(User::where('email', 'aviso@example.test')->exists())->toBeTrue()
        ->and(session()->has('warning'))->toBeTrue();
});

// -------------------------------------------------------------------------
// UserEdit: autorizaciones, validación, roles, transacciones y correos.
// -------------------------------------------------------------------------

test('UserEdit solicita confirmación sin persistir cambios', function (): void {
    $target = baseUserTarget($this->adminRole);

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('name', 'Otro nombre')
        ->call('confirmSave')
        ->assertHasNoErrors()
        ->assertDispatched('open-confirm-modal', confirmEvent: 'user-edit-save-confirmed');

    expect($target->fresh()->name)->not->toBe('Otro nombre');
});

test('UserEdit guarda nombre y roles sin reenviar enlace si correo no cambia', function (): void {
    $target = baseUserTarget($this->adminRole);
    Password::shouldReceive('sendResetLink')->never();

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('name', '  Nombre Modificado  ')
        ->set('roleIds', [$this->operatorRole->id])
        ->call('save', $target->id)
        ->assertHasNoErrors()
        ->assertRedirectToRoute('users.index');

    $updated = $target->fresh();
    expect($updated->name)->toBe('Nombre Modificado')
        ->and($updated->hasRole('Operator', 'web'))->toBeTrue()
        ->and($updated->hasRole('Admin', 'web'))->toBeFalse();
});

test('UserEdit alerta cuando no hay cambios', function (): void {
    $target = baseUserTarget($this->adminRole);

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->call('confirmSave')
        ->assertDispatched('app-alert', type: 'info', message: 'No hay cambios para guardar.')
        ->assertNoRedirect();
});

test('UserEdit rechaza correo duplicado', function (): void {
    $target = baseUserTarget($this->adminRole);
    $another = User::factory()->create(['email' => 'duplicado@example.test']);

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('email', $another->email)
        ->call('confirmSave')
        ->assertHasErrors(['email' => 'unique']);
});

test('UserEdit exige rol válido para un usuario ordinario', function (): void {
    $target = baseUserTarget($this->adminRole);

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('roleIds', [])
        ->call('confirmSave')
        ->assertHasErrors(['roleIds' => 'required']);
});

test('UserEdit impide editar la cuenta primaria ID 1', function (): void {
    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $this->primary])
        ->assertForbidden();
});

test('UserEdit impide editar una cuenta propia', function (): void {
    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $this->actor])
        ->assertForbidden();
});

test('UserEdit impide editar si el actor carece de autorización', function (): void {
    $target = baseUserTarget($this->adminRole);
    $unprivileged = User::factory()->create();

    Livewire::actingAs($unprivileged)
        ->test(UserEdit::class, ['user' => $target])
        ->assertForbidden();
});

test('UserEdit no modifica roles de otra cuenta SuperAdmin', function (): void {
    $target = User::factory()->create();
    $target->assignRole($this->superAdminRole);

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('name', 'SuperAdmin Actualizado')
        ->set('roleIds', [$this->adminRole->id])
        ->call('save', $target->id)
        ->assertHasNoErrors()
        ->assertRedirectToRoute('users.index');

    $updated = $target->fresh();
    expect($updated->name)->toBe('SuperAdmin Actualizado')
        ->and($updated->hasRole('SuperAdmin', 'web'))->toBeTrue()
        ->and($updated->hasRole('Admin', 'web'))->toBeFalse();
});

test('UserEdit cambia email, invalida verificación y solicita enlace', function (): void {
    $target = baseUserTarget($this->adminRole);
    Password::shouldReceive('sendResetLink')
        ->once()
        ->with(['email' => 'nuevo-correo@example.test'])
        ->andReturn(Password::RESET_LINK_SENT);

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('email', 'NUEVO-CORREO@EXAMPLE.TEST')
        ->call('save', $target->id)
        ->assertHasNoErrors()
        ->assertRedirectToRoute('users.index');

    $updated = $target->fresh();
    expect($updated->email)->toBe('nuevo-correo@example.test')
        ->and($updated->email_verified_at)->toBeNull();
});

test('UserEdit conserva el correo cambiado si falla el enlace', function (): void {
    $target = baseUserTarget($this->adminRole);
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andThrow(new RuntimeException('FALLO_DE_CORREO'));

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('email', 'fallo-correo@example.test')
        ->call('save', $target->id)
        ->assertRedirect(route('users.edit', ['user' => $target->id]));

    expect($target->fresh()->email)->toBe('fallo-correo@example.test')
        ->and(session()->has('warning'))->toBeTrue();
});

test('UserEdit suspende con soft delete y permite restaurar sin perder roles', function (): void {
    $target = baseUserTarget($this->adminRole);

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->call('confirmSuspend')
        ->assertDispatched('open-confirm-modal', confirmEvent: 'user-edit-suspend-confirmed')
        ->call('suspend', $target->id)
        ->assertRedirectToRoute('users.index');

    expect(User::withTrashed()->findOrFail($target->id)->trashed())->toBeTrue();

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => User::withTrashed()->findOrFail($target->id)])
        ->call('confirmRestore')
        ->assertDispatched('open-confirm-modal', confirmEvent: 'user-edit-restore-confirmed')
        ->call('restore', $target->id)
        ->assertRedirectToRoute('users.index');

    $restored = User::findOrFail($target->id);
    expect($restored->trashed())->toBeFalse()
        ->and($restored->hasRole('Admin', 'web'))->toBeTrue();
});

test('UserEdit rechaza un ID de confirmación que no coincide', function (): void {
    $target = baseUserTarget($this->adminRole);

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->call('save', $target->id + 100)
        ->assertForbidden();

    expect($target->fresh()->name)->toBe($target->name);
});

test('UserEdit no envía enlace si el correo visible no coincide con el guardado', function (): void {
    $target = baseUserTarget($this->adminRole);
    Password::shouldReceive('sendResetLink')->never();

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('email', 'pendiente@example.test')
        ->call('confirmResetPassword')
        ->assertHasErrors(['email']);
});

test('UserEdit solicita restablecer contraseña en forma manual', function (): void {
    $target = baseUserTarget($this->adminRole);
    Password::shouldReceive('sendResetLink')
        ->once()
        ->with(['email' => $target->email])
        ->andReturn(Password::RESET_LINK_SENT);

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->call('confirmResetPassword')
        ->assertDispatched('open-confirm-modal', confirmEvent: 'user-edit-reset-password-confirmed')
        ->call('resetPassword', $target->id)
        ->assertDispatched('app-alert', type: 'success', message: 'Se solicitó el envío del enlace de restablecimiento.');
});

test('UserEdit indica limitación de envío del enlace', function (): void {
    $target = baseUserTarget($this->adminRole);
    Password::shouldReceive('sendResetLink')
        ->once()
        ->andReturn(Password::RESET_THROTTLED);

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->call('resetPassword', $target->id)
        ->assertDispatched('app-alert', type: 'warning', message: 'Ya existe una solicitud reciente. Intenta más tarde.');
});

test('UserEdit revierte el cambio y registra un error si falla la actualización', function (): void {
    $target = baseUserTarget($this->adminRole);
    $originalName = $target->name;

    User::saving(function (User $user) use ($target): void {
        if ($user->is($target)) {
            throw new RuntimeException('FALLO_CONTROLADO_EDICION');
        }
    });

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('name', 'Nombre NO Guardado')
        ->call('save', $target->id)
        ->assertDispatched('app-alert', type: 'error', message: 'No fue posible actualizar el usuario. Intenta nuevamente.')
        ->assertNoRedirect();

    expect($target->fresh()->name)->toBe($originalName)
        ->and(ErrorLog::where('exception', RuntimeException::class)->exists())->toBeTrue();
});

test('UserEdit registra fallo de suspensión sin suspender la cuenta', function (): void {
    $target = baseUserTarget($this->adminRole);

    User::deleting(function (User $user) use ($target): void {
        if ($user->is($target)) {
            throw new RuntimeException('FALLO_CONTROLADO_SUSPENSION');
        }
    });

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => $target])
        ->call('suspend', $target->id)
        ->assertDispatched('app-alert', type: 'error', message: 'No fue posible suspender al usuario. Intenta nuevamente.')
        ->assertNoRedirect();

    expect(User::withTrashed()->findOrFail($target->id)->trashed())->toBeFalse();
});

test('UserEdit registra fallo de restauración sin reactivar la cuenta', function (): void {
    $target = baseUserTarget($this->adminRole);
    $target->delete();

    User::restoring(function (User $user) use ($target): void {
        if ($user->is($target)) {
            throw new RuntimeException('FALLO_CONTROLADO_RESTAURACION');
        }
    });

    Livewire::actingAs($this->actor)
        ->test(UserEdit::class, ['user' => User::withTrashed()->findOrFail($target->id)])
        ->call('restore', $target->id)
        ->assertDispatched('app-alert', type: 'error', message: 'No fue posible restaurar al usuario. Intenta nuevamente.')
        ->assertNoRedirect();

    expect(User::withTrashed()->findOrFail($target->id)->trashed())->toBeTrue();
});