<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        // Roles existentes.
        Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        /*
         * Permisos actuales de usuarios.
         * Se conserva su asignación al rol Admin.
         */
        

        /*
         * Nuevos permisos de Roles, Permisos y Perfil.
         * Se registran, pero no se asignan
         * automáticamente al rol Admin.
         */
        $permissions = [


            // Users

            'users.index',
            'users.create',
            'users.edit',
            'users.destroy',
            'users.suspend',
            'users.restore',

            // Roles
            'roles.index',
            'roles.create',
            'roles.show',
            'roles.edit',
            'roles.destroy',

            // permissions
            'permissions.index',
            'permissions.create',
            'permissions.show',
            'permissions.edit',
            'permissions.destroy',

            // profile
            'profiles.view',
            'profiles.update',
            'profiles.delete',
            'profiles.changePassword',

            // Registros de errores (no asignados por defecto a Admin)
            'errors.index',
            'errors.show',
        ];

        // Crear únicamente los permisos que faltan.
        foreach ($permissions as $Permission) {
            Permission::firstOrCreate([
                'name' => $Permission,
                'guard_name' => 'web',
            ]);
        }

        // Actualizar caché antes de asignar permisos.
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        // Conservar permisos existentes de Admin.
        foreach ($permissions as $permission) {
            if (in_array($permission, ['errors.index', 'errors.show'], true)) {
                continue;
            }
            $admin->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
