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


        Role::create([
            'name' => 'SuperAdmin',
            'guard_name' => 'web',
        ]);
        

        $role= Role::create([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        Permission::create([
            'name' => 'users.index',
            'guard_name' => 'web',
        ])->syncRoles($role);

        Permission::create([
            'name' => 'users.create',
            'guard_name' => 'web',
        ])->syncRoles($role);

        Permission::create([
            'name' => 'users.edit',
            'guard_name' => 'web',
        ])->syncRoles($role);

        Permission::create([
            'name' => 'users.destroy',
            'guard_name' => 'web',
        ])->syncRoles($role);

        Permission::create([
            'name' => 'users.suspend',
            'guard_name' => 'web',
        ])->syncRoles($role);

        Permission::create([
            'name' => 'users.restore',
            'guard_name' => 'web',
        ])->syncRoles($role);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}