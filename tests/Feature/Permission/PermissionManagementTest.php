<?php

use App\Livewire\Permission\CreatePermission;
use App\Livewire\Permission\EditPermission;
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

it('deniega el acceso a permisos sin autorizacion', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)->get(route('permissions.index'))->assertForbidden();
    $this->actingAs($actor)->get(route('permissions.create'))->assertForbidden();
});

it('valida nombre obligatorio antes de crear permisos', function (): void {
    Livewire::actingAs($this->superAdmin)
        ->test(CreatePermission::class)
        ->call('save')
        ->assertHasErrors(['permission' => 'required']);
});

it('crea permisos nuevos en guard web sin asignarlos automaticamente', function (): void {
    Livewire::actingAs($this->superAdmin)
        ->test(CreatePermission::class)
        ->set('permission', 'inventario.index')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('permissions.index');

    $permission = Permission::findByName('inventario.index', 'web');

    expect($permission->guard_name)->toBe('web')
        ->and($permission->roles()->count())->toBe(0)
        ->and($permission->users()->count())->toBe(0);
});

it('rechaza permisos duplicados dentro del mismo guard', function (): void {
    Permission::create(['name' => 'inventario.index', 'guard_name' => 'web']);

    Livewire::actingAs($this->superAdmin)
        ->test(CreatePermission::class)
        ->set('permission', 'inventario.index')
        ->call('save')
        ->assertHasErrors(['permission' => 'unique']);

    expect(Permission::where('name', 'inventario.index')
        ->where('guard_name', 'web')->count())->toBe(1);
});

it('recorta espacios en el nombre de permisos al crearlos', function (): void {
    Livewire::actingAs($this->superAdmin)
        ->test(CreatePermission::class)
        ->set('permission', '  inventario.create  ')
        ->call('save')
        ->assertHasNoErrors();

    expect(Permission::where('name', 'inventario.create')
        ->where('guard_name', 'web')->exists())->toBeTrue();
});

it('edita un permiso sin relaciones con roles', function (): void {
    $permission = Permission::create([
        'name' => 'inventario.index',
        'guard_name' => 'web',
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(EditPermission::class, ['permissionId' => (int) $permission->id])
        ->set('permission', 'inventario.listar')
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('permissions.index');

    expect($permission->fresh()->name)->toBe('inventario.listar');
});

it('bloquea la edicion de permisos reservados del sistema', function (): void {
    $permission = Permission::create([
        'name' => 'users.create',
        'guard_name' => 'web',
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(EditPermission::class, ['permissionId' => (int) $permission->id])
        ->assertForbidden();
});

it('impide modificar permisos asociados a roles', function (): void {
    $permission = Permission::create([
        'name' => 'inventario.index',
        'guard_name' => 'web',
    ]);
    $role = Role::create(['name' => 'Operador', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    Livewire::actingAs($this->superAdmin)
        ->test(EditPermission::class, ['permissionId' => (int) $permission->id])
        ->assertForbidden();
});

it('no permite eliminar permisos asociados directamente a usuarios', function (): void {
    $permission = Permission::create([
        'name' => 'inventario.index',
        'guard_name' => 'web',
    ]);
    $user = User::factory()->create();
    $user->givePermissionTo($permission);

    Livewire::actingAs($this->superAdmin)
        ->test(EditPermission::class, ['permissionId' => (int) $permission->id])
        ->call('confirmDelete')
        ->assertForbidden();

    expect(Permission::whereKey($permission->id)->exists())->toBeTrue();
});

it('elimina un permiso sin relaciones despues de confirmar', function (): void {
    $permission = Permission::create([
        'name' => 'inventario.index',
        'guard_name' => 'web',
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(EditPermission::class, ['permissionId' => (int) $permission->id])
        ->call('confirmDelete')
        ->assertDispatched('open-confirm-modal')
        ->call('delete', (int) $permission->id)
        ->assertHasNoErrors();

    expect(Permission::whereKey($permission->id)->exists())->toBeFalse();
});

it('impide borrar permisos sin una confirmacion previa', function (): void {
    $permission = Permission::create([
        'name' => 'inventario.index',
        'guard_name' => 'web',
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(EditPermission::class, ['permissionId' => (int) $permission->id])
        ->call('delete', (int) $permission->id)
        ->assertForbidden();

    expect(Permission::whereKey($permission->id)->exists())->toBeTrue();
});

it('rechaza usar un permiso de otro guard en el editor web', function (): void {
    $permission = Permission::create([
        'name' => 'inventario.api',
        'guard_name' => 'api',
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(EditPermission::class, ['permissionId' => (int) $permission->id])
        ->assertNotFound();
});

it('mantiene prohibida la eliminacion forzada de permisos', function (): void {
    $permission = Permission::create([
        'name' => 'inventario.index',
        'guard_name' => 'web',
    ]);

    expect(Gate::forUser($this->superAdmin)->allows('forceDelete', $permission))->toBeFalse();
});
