<?php

use App\Livewire\Role\CreateRole;
use App\Livewire\Role\EditRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole(Role::firstOrCreate([
        'name' => 'SuperAdmin',
        'guard_name' => 'web',
    ]));
});

it('rechaza el acceso a roles para usuarios no autorizados', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)->get(route('roles.index'))->assertForbidden();
    $this->actingAs($actor)->get(route('roles.create'))->assertForbidden();
});

it('valida nombre y permisos obligatorios al crear un rol', function (): void {
    Livewire::actingAs($this->superAdmin)
        ->test(CreateRole::class)
        ->call('save')
        ->assertHasErrors(['role', 'permissionIds']);
});

it('crea un rol con exactamente los permisos seleccionados', function (): void {
    $first = Permission::create(['name' => 'inventario.index', 'guard_name' => 'web']);
    $second = Permission::create(['name' => 'inventario.create', 'guard_name' => 'web']);

    Livewire::actingAs($this->superAdmin)
        ->test(CreateRole::class)
        ->set('role', 'Operador')
        ->set('permissionIds', [(int) $first->id, (int) $second->id])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('roles.index');

    $role = Role::findByName('Operador', 'web');

    expect($role->permissions->pluck('name')->sort()->values()->all())
        ->toBe(['inventario.create', 'inventario.index']);
});

it('impide crear el rol reservado SuperAdmin y duplicar nombres', function (): void {
    $permission = Permission::create(['name' => 'inventario.index', 'guard_name' => 'web']);
    Role::create(['name' => 'Operador', 'guard_name' => 'web']);

    Livewire::actingAs($this->superAdmin)
        ->test(CreateRole::class)
        ->set('role', 'SuperAdmin')
        ->set('permissionIds', [(int) $permission->id])
        ->call('save')
        ->assertHasErrors(['role']);

    Livewire::actingAs($this->superAdmin)
        ->test(CreateRole::class)
        ->set('role', 'Operador')
        ->set('permissionIds', [(int) $permission->id])
        ->call('save')
        ->assertHasErrors(['role' => 'unique']);
});

it('rechaza permisos duplicados y permisos de guard distinto', function (): void {
    $allowed = Permission::create(['name' => 'inventario.index', 'guard_name' => 'web']);
    $otherGuard = Permission::create(['name' => 'inventario.api', 'guard_name' => 'api']);

    Livewire::actingAs($this->superAdmin)
        ->test(CreateRole::class)
        ->set('role', 'Operador')
        ->set('permissionIds', [(int) $allowed->id, (int) $allowed->id])
        ->call('save')
        ->assertHasErrors(['permissionIds.1' => 'distinct']);

    Livewire::actingAs($this->superAdmin)
        ->test(CreateRole::class)
        ->set('role', 'Operador')
        ->set('permissionIds', [(int) $otherGuard->id])
        ->call('save')
        ->assertHasErrors(['permissionIds.0' => 'in']);

    expect(Role::where('name', 'Operador')->exists())->toBeFalse();
});

it('impide delegar permisos que el editor no posee', function (): void {
    $actor = User::factory()->create();
    $actor->givePermissionTo(Permission::create(['name' => 'roles.create', 'guard_name' => 'web']));
    $unowned = Permission::create(['name' => 'facturas.destroy', 'guard_name' => 'web']);

    Livewire::actingAs($actor)
        ->test(CreateRole::class)
        ->set('role', 'No autorizado')
        ->set('permissionIds', [(int) $unowned->id])
        ->call('save')
        ->assertHasErrors(['permissionIds.0' => 'in']);

    expect(Role::where('name', 'No autorizado')->exists())->toBeFalse();
});

it('edita los permisos de un rol y elimina los desmarcados', function (): void {
    $role = Role::create(['name' => 'Operador', 'guard_name' => 'web']);
    $first = Permission::create(['name' => 'inventario.index', 'guard_name' => 'web']);
    $second = Permission::create(['name' => 'inventario.edit', 'guard_name' => 'web']);
    $role->syncPermissions([$first, $second]);

    Livewire::actingAs($this->superAdmin)
        ->test(EditRole::class, ['roleId' => (int) $role->id])
        ->set('permissionIds', [(int) $first->id])
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('roles.index');

    expect($role->fresh()->permissions->pluck('name')->all())->toBe(['inventario.index']);
});

it('conserva permisos del rol que un editor no puede administrar', function (): void {
    $actor = User::factory()->create();
    $actor->givePermissionTo(
        Permission::create(['name' => 'roles.index', 'guard_name' => 'web'])
    );
    $actor->givePermissionTo(
        Permission::create(['name' => 'roles.edit', 'guard_name' => 'web'])
    );
    $allowed = Permission::create(['name' => 'inventario.index', 'guard_name' => 'web']);
    $protected = Permission::create(['name' => 'finanzas.aprobar', 'guard_name' => 'web']);
    $actor->givePermissionTo($allowed);

    $role = Role::create(['name' => 'Operador', 'guard_name' => 'web']);
    $role->syncPermissions([$allowed, $protected]);

    Livewire::actingAs($actor)
        ->test(EditRole::class, ['roleId' => (int) $role->id])
        ->set('permissionIds', [])
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('roles.index');

    expect($role->fresh()->hasPermissionTo($protected))->toBeTrue()
        ->and($role->fresh()->hasPermissionTo($allowed))->toBeFalse();
});

it('bloquea la edicion del rol reservado SuperAdmin', function (): void {
    Livewire::actingAs($this->superAdmin)
        ->test(EditRole::class, [
            'roleId' => (int) Role::findByName('SuperAdmin', 'web')->id,
        ])
        ->assertForbidden();
});

it('mantiene denegado el borrado fisico de roles', function (): void {
    $role = Role::create(['name' => 'Operador', 'guard_name' => 'web']);

    expect(Gate::forUser($this->superAdmin)->allows('forceDelete', $role))->toBeFalse();
});
