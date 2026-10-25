<?php

use App\Livewire\User\UserCreate;
use App\Livewire\User\UserEdit;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('un usuario sin permiso no puede abrir la edición', function () {
    $actor = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($actor)->get(route('users.edit', $target))->assertForbidden();
    Livewire::actingAs($actor)->test(UserEdit::class, ['user' => $target])->assertForbidden();
});


test('ni SuperAdmin puede editar administrativamente la cuenta principal ID 1', function () {
    $principal = User::factory()->create();
    $principal->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));

    $this->actingAs($principal)->get(route('users.edit', $principal))->assertForbidden();

    Livewire::actingAs($principal)
        ->test(UserEdit::class, ['user' => $principal])
        ->assertForbidden();
});

test('el usuario sin SuperAdmin no accede a la cuenta principal', function () {
    $principal = User::factory()->create();
    $principal->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));
    $admin = User::factory()->create();
    $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $admin->assignRole($role);

    foreach (['users.index', 'users.edit'] as $permission) {
        $role->givePermissionTo(\Spatie\Permission\Models\Permission::create([
            'name' => $permission, 'guard_name' => 'web',
        ]));
    }

    $this->actingAs($admin)->get(route('users.edit', $principal))->assertForbidden();
    $this->actingAs($admin)->get(route('users.show', $principal))->assertForbidden();
    Livewire::actingAs($admin)->test(UserEdit::class, ['user' => $principal])->assertForbidden();

    expect((new \App\Livewire\User\UserTable())->datasource()->pluck('id')->all())
        ->not->toContain($principal->id);
});


test('la cuenta principal conserva su rol SuperAdmin al bloquear la edición', function () {
    $principal = User::factory()->create();
    $role = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $principal->assignRole($role);
    $actor = User::factory()->create();
    $actor->assignRole($role);

    Livewire::actingAs($actor)
        ->test(UserEdit::class, ['user' => $principal])
        ->assertForbidden();

    expect($principal->fresh()->hasRole('SuperAdmin', 'web'))->toBeTrue();
});

test('la creación solicita confirmación antes de guardar', function () {
    $actor = User::factory()->create();
    $actor->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));
    $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);

    Livewire::actingAs($actor)->test(UserCreate::class)
        ->set('name', 'Nuevo usuario')
        ->set('email', 'nuevo@example.com')
        ->set('roleIds', [$role->id])
        ->call('confirmSave')
        ->assertDispatched('open-confirm-modal');

    expect(User::where('email', 'nuevo@example.com')->exists())->toBeFalse();
});

test('cambiar correo actualiza roles y solicita un enlace al correo nuevo', function () {
    Notification::fake();

    $actor = User::factory()->create();
    $actor->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));
    $target = User::factory()->create();
    $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);

    Livewire::actingAs($actor)->test(UserEdit::class, ['user' => $target])
        ->set('email', 'actualizado@example.com')
        ->set('roleIds', [$role->id])
        ->call('confirmSave')
        ->assertDispatched('open-confirm-modal')
        ->call('save', $target->id);

    $target->refresh();

    expect($target->email)->toBe('actualizado@example.com')
        ->and($target->email_verified_at)->toBeNull()
        ->and($target->hasRole('Admin', 'web'))->toBeTrue();

    Notification::assertSentTo($target, ResetPassword::class);
});


test('editar otro SuperAdmin conserva sus roles aun con roleIds manipulados', function () {
    User::factory()->create(); // Cuenta principal protegida.
    $role = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->assignRole($role);
    $target = User::factory()->create();
    $target->assignRole($role);
    $otherRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);

    Livewire::actingAs($actor)
        ->test(UserEdit::class, ['user' => $target])
        ->set('name', 'SuperAdministrador Actualizado')
        ->set('roleIds', [$otherRole->id])
        ->call('confirmSave')
        ->assertDispatched('open-confirm-modal')
        ->call('save', $target->id)
        ->assertHasNoErrors();

    $target->refresh();
    expect($target->name)->toBe('SuperAdministrador Actualizado')
        ->and($target->hasRole('SuperAdmin', 'web'))->toBeTrue()
        ->and($target->hasRole('Admin', 'web'))->toBeFalse();
});
