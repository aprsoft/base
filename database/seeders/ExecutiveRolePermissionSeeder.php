<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ExecutiveRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    

        $executive = Role::firstOrCreate([
            'name' => 'Executive',
            'guard_name' => 'web',
        ]);      
       
        $permissions = [

            // Users

            'users.index',
            'users.create',
            'users.edit',
            // 'users.destroy',
            'users.suspend',
            'users.restore',
            
        ];

       

        // Actualizar caché antes de asignar permisos.
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        // Conservar permisos existentes de Executive.
        foreach ($permissions as $permission) {
            $executive->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
