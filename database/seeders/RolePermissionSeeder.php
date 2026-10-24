<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos antes de ejecutar.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Crear roles
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $profileBasic = Role::firstOrCreate([
            'name' => 'profileBasic',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SuperAdmin: acceso global mediante Gate::before()
        |--------------------------------------------------------------------------
        |
        | No necesita permisos asignados directamente al rol.
        | AppServiceProvider concede el acceso global.
        |
        */

        $superAdmin->syncPermissions([]);

        /*
        |--------------------------------------------------------------------------
        | Crear permisos y asignarlos a Admin
        |--------------------------------------------------------------------------
        */

        $permissions = [
            'users.index',
            'users.create',
            'users.edit',
            'users.destroy',
            'profile.password.update',
            'profile.view',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $admin->givePermissionTo($permission);
        }

        // Refrescar caché después de configurar permisos.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
