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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role1 = Role::create([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);

        $role2 = Role::create([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'central.users.index',
            'guard_name' => 'web',
        ])->syncRoles($role1, $role2);

        Permission::create([
            'name' => 'central.users.create',
            'guard_name' => 'web',
        ])->syncRoles($role1, $role2);

        Permission::create([
            'name' => 'central.users.edit',
            'guard_name' => 'web',
        ])->syncRoles($role1);

        Permission::create([
            'name' => 'central.users.destroy',
            'guard_name' => 'web',
        ])->syncRoles($role1);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}