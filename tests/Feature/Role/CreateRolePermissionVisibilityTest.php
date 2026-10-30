<?php

use App\Livewire\Permission\CreatePermission;
use App\Livewire\Role\CreateRole;
use App\Models\User;
use App\Support\Permissions\PermissionCatalog;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('muestra permisos nuevos del guard web a SuperAdmin al crear un rol', function (): void {
    $superAdmin = User::factory()->create();
    $superAdminRole = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $superAdmin->assignRole($superAdminRole);

    $first = Permission::create(['name' => 'inventario.index', 'guard_name' => 'web']);
    $second = Permission::create(['name' => 'inventario.create', 'guard_name' => 'web']);

    Livewire::actingAs($superAdmin)
        ->test(CreateRole::class)
        ->assertViewHas('permissions', fn ($permissions): bool =>
            $permissions->contains('id', $first->id)
            && $permissions->contains('id', $second->id)
        )
        ->assertViewHas('permissionGroups', fn ($groups): bool =>
            $groups->contains(
                fn (array $group): bool =>
                    $group['module'] === 'inventario'
                    && in_array((int) $first->id, $group['ids'], true)
                    && in_array((int) $second->id, $group['ids'], true)
            )
        )
        ->assertSee('inventario.index')
        ->assertSee('inventario.create');
});

it('reproduce que crear un permiso no lo hace asignable para un editor comun', function (): void {
    $editor = User::factory()->create();
    $createRoles = Permission::create(['name' => 'roles.create', 'guard_name' => 'web']);
    $createPermissions = Permission::create(['name' => 'permissions.create', 'guard_name' => 'web']);
    $editor->givePermissionTo([$createRoles, $createPermissions]);

    // El editor puede crear permisos, pero no puede delegar permisos que no tiene.
    Livewire::actingAs($editor)
        ->test(CreatePermission::class)
        ->set('permission', 'inventario.create')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('permissions.index'));

    $created = Permission::findByName('inventario.create', 'web');

    Livewire::actingAs($editor)
        ->test(CreateRole::class)
        ->assertViewHas('permissions', fn ($permissions): bool =>
            ! $permissions->contains('id', $created->id)
        )
        ->assertDontSee('inventario.create');
});

it('muestra el permiso nuevo al editor cuando este recibe autorizacion para asignarlo', function (): void {
    $editor = User::factory()->create();
    $editor->givePermissionTo(
        Permission::create(['name' => 'roles.create', 'guard_name' => 'web'])
    );

    $permission = Permission::create([
        'name' => 'inventario.create',
        'guard_name' => 'web',
    ]);

    $editor->givePermissionTo($permission);

    Livewire::actingAs($editor)
        ->test(CreateRole::class)
        ->assertViewHas('permissions', fn ($permissions): bool =>
            $permissions->contains('id', $permission->id)
        )
        ->assertSee('inventario.create');
});

it('no mezcla permisos de otro guard aunque el usuario sea SuperAdmin', function (): void {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::create([
        'name' => 'SuperAdmin',
        'guard_name' => 'web',
    ]));

    $permission = Permission::create([
        'name' => 'inventario.api',
        'guard_name' => 'api',
    ]);

    Livewire::actingAs($superAdmin)
        ->test(CreateRole::class)
        ->assertViewHas('permissions', fn ($permissions): bool =>
            ! $permissions->contains('id', $permission->id)
        )
        ->assertDontSee('inventario.api');
});

it('agrupa automaticamente cualquier modulo sin modificar la vista Blade', function (): void {
    $permission = Permission::create([
        'name' => 'facturacion.dte.emitir',
        'guard_name' => 'web',
    ]);

    $catalog = app(PermissionCatalog::class);
    $groups = $catalog->groups(collect([$permission]));

    expect($groups)->toHaveCount(1)
        ->and($groups->first()['module'])->toBe('facturacion')
        ->and($groups->first()['ids'])->toBe([(int) $permission->id])
        ->and($catalog->forAlpine($groups)[0]['names'])->toBe(['facturacion.dte.emitir']);
});
