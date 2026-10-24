<?php

use App\Livewire\User\UserShow;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function makeUserViewer(): User
{
    $permission = Permission::firstOrCreate([
        'name' => 'users.index',
        'guard_name' => 'web',
    ]);

    $role = Role::firstOrCreate([
        'name' => 'Admin',
        'guard_name' => 'web',
    ]);

    $role->givePermissionTo($permission);

    $viewer = User::factory()->create();
    $viewer->assignRole($role);

    return $viewer;
}

test('mostrar usuario exige el permiso de listado', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.show', $target))
        ->assertForbidden();

    Livewire::actingAs($user)
        ->test(UserShow::class, ['user' => $target])
        ->assertForbidden();
});

test('se muestra el perfil del usuario y sus roles', function () {
    $viewer = makeUserViewer();
    $target = User::factory()->create();
    $target->assignRole('Admin');

    $this->actingAs($viewer)
        ->get(route('users.show', $target))
        ->assertOk()
        ->assertSee($target->name)
        ->assertSee($target->email);

    Livewire::actingAs($viewer)
        ->test(UserShow::class, ['user' => $target])
        ->assertSee($target->name)
        ->assertSee('Roles asignados')
        ->assertSee('Admin');
});

test('se puede consultar el estado de un usuario suspendido', function () {
    $viewer = makeUserViewer();
    $target = User::factory()->create();
    $target->delete();

    $this->actingAs($viewer)
        ->get(route('users.show', ['user' => $target->id]))
        ->assertOk()
        ->assertSee('Suspendido');
});

test('la cuenta principal no puede mostrarse', function () {
    $firstUser = User::factory()->create();
    $viewer = makeUserViewer();

    $firstUser->forceFill(['system_account_key' => User::PRIMARY_SUPER_ADMIN_KEY])->save();

    $this->actingAs($viewer)
        ->get(route('users.show', $firstUser))
        ->assertForbidden();

    Livewire::actingAs($viewer)
        ->test(UserShow::class, ['user' => $firstUser])
        ->assertForbidden();
});

test('SuperAdmin no puede visualizar la cuenta principal', function () {
    $principal = User::factory()->create();
    $principal->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));

    $principal->forceFill(['system_account_key' => User::PRIMARY_SUPER_ADMIN_KEY])->save();
    $this->actingAs($principal)->get(route('users.show', $principal))->assertForbidden();
    Livewire::actingAs($principal)
        ->test(UserShow::class, ['user' => $principal])->assertForbidden();
});

test('show del SuperAdmin presenta acceso global sin listado de roles', function () {
    $principal = User::factory()->create();
    $principal->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));

    Livewire::actingAs($principal)
        ->test(UserShow::class, ['user' => $principal])
        ->assertSee('Acceso administrativo global')
        ->assertDontSee('Roles asignados');
});
