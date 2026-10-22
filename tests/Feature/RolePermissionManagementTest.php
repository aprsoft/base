<?php

use App\Livewire\Permission\CreatePermission;
use App\Livewire\Permission\EditPermission;
use App\Livewire\Role\CreateRole;
use App\Livewire\Role\EditRole;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('un usuario sin SuperAdmin no puede consultar ni administrar roles y permisos', function () {
    $actor = User::factory()->create();
    $role = Role::create(['name' => 'Operador', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'reports.read', 'guard_name' => 'web']);

    foreach ([
        route('roles.index'),
        route('roles.create'),
        route('roles.show', $role->id),
        route('roles.edit', $role->id),
        route('permissions.index'),
        route('permissions.create'),
        route('permissions.show', $permission->id),
        route('permissions.edit', $permission->id),
    ] as $url) {
        $this->actingAs($actor)->get($url)->assertForbidden();
    }

    Livewire::actingAs($actor)->test(CreateRole::class)->assertForbidden();
    Livewire::actingAs($actor)->test(EditRole::class, ['roleId' => $role->id])->assertForbidden();
    Livewire::actingAs($actor)->test(CreatePermission::class)->assertForbidden();
    Livewire::actingAs($actor)->test(EditPermission::class, ['permissionId' => $permission->id])->assertForbidden();
});

test('SuperAdmin puede abrir las seis pantallas profesionales', function () {
    $actor = User::factory()->create();
    $adminRole = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $actor->assignRole($adminRole);

    $role = Role::create(['name' => 'Supervisor', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'reports.read', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    foreach ([
        route('roles.create'),
        route('roles.show', $role->id),
        route('roles.edit', $role->id),
        route('permissions.create'),
        route('permissions.show', $permission->id),
        route('permissions.edit', $permission->id),
    ] as $url) {
        $this->actingAs($actor)->get($url)->assertOk();
    }
});

test('crear permiso redirige a la ruta vigente', function () {
    $actor = User::factory()->create();
    $role = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $actor->assignRole($role);

    Livewire::actingAs($actor)->test(CreatePermission::class)
        ->set('permission', 'reports.export')
        ->call('save')
        ->assertRedirect(route('permissions.index'));

    expect(Permission::where('name', 'reports.export')->where('guard_name', 'web')->exists())->toBeTrue();
});

test('crear rol asigna permisos del guard web', function () {
    $actor = User::factory()->create();
    $actor->assignRole(Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']));
    $permission = Permission::create(['name' => 'reports.read', 'guard_name' => 'web']);

    Livewire::actingAs($actor)->test(CreateRole::class)
        ->set('role', 'Auditor')
        ->set('permissionIds', [$permission->id])
        ->call('save')
        ->assertRedirect(route('roles.index'));

    expect(Role::findByName('Auditor', 'web')->hasPermissionTo($permission))->toBeTrue();
});
