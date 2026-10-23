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

test('el usuario con ID 1 no puede mostrarse', function () {
    $firstUser = User::factory()->create();
    $viewer = makeUserViewer();

    expect($firstUser->id)->toBe(1);

    $this->actingAs($viewer)
        ->get(route('users.show', $firstUser))
        ->assertForbidden();

    Livewire::actingAs($viewer)
        ->test(UserShow::class, ['user' => $firstUser])
        ->assertForbidden();
});

test('SuperAdmin puede visualizar el usuario ID 1', function () {
    $principal = User::factory()->create();
    $principal->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));

    $this->actingAs($principal)->get(route('users.show', $principal))->assertOk();
    Livewire::actingAs($principal)
        ->test(UserShow::class, ['user' => $principal])->assertHasNoErrors();
});
