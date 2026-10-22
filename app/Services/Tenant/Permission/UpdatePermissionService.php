<?php

namespace App\Services\Tenant\Permission;

use DomainException;
use Spatie\Permission\Models\Permission;

class UpdatePermissionService
{
    public function update(int $id, string $name): string
    {
        $permission = Permission::query()
            ->where('guard_name', 'tenant')
            ->find($id);

        if (! $permission) {
            throw new DomainException(
                'El permiso no existe o ya no está disponible.'
            );
        }

        $permission->update([
            'name' => $name,
        ]);

        return $permission->name;
    }
}