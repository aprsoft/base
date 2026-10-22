<?php

namespace App\Services\Tenant\Permission;

use DomainException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class DeletePermissionService
{
    public function delete(int $id): string
    {
        return DB::transaction(function () use ($id): string {           

            $permission = Permission::query()
                ->where('guard_name', 'tenant')
                ->lockForUpdate()
                ->find($id);

     

            if (! $permission) {
                throw new DomainException(
                    'El permiso no existe o ya no está disponible.'
                );
            }

            $rolesCount = $permission->roles()->count();

            $permissionPivotKey = config(
                'permission.column_names.permission_pivot_key'
            ) ?? 'permission_id';

            $modelHasPermissionsTable = config(
                'permission.table_names.model_has_permissions'
            );

            if (
                ! is_string($modelHasPermissionsTable) ||
                $modelHasPermissionsTable === ''
            ) {
                throw new \RuntimeException(
                    'La tabla model_has_permissions no está configurada correctamente.'
                );
            }

            $directAssignmentsCount = DB::table(
                $modelHasPermissionsTable
            )
                ->where($permissionPivotKey, $permission->id)
                ->count();

            if ($rolesCount > 0 || $directAssignmentsCount > 0) {
                throw new DomainException(
                    $this->buildInUseMessage(
                        $permission->name,
                        $rolesCount,
                        $directAssignmentsCount
                    )
                );
            }

            $permissionName = $permission->name;

            $permission->delete();

            return $permissionName;
        });
    }

    private function buildInUseMessage(
        string $permissionName,
        int $rolesCount,
        int $directAssignmentsCount
    ): string {
        $associations = [];

        if ($rolesCount > 0) {
            $associations[] = $rolesCount === 1
                ? '1 rol'
                : "{$rolesCount} roles";
        }

        if ($directAssignmentsCount > 0) {
            $associations[] = $directAssignmentsCount === 1
                ? '1 asignación directa'
                : "{$directAssignmentsCount} asignaciones directas";
        }

        return sprintf(
            'No se puede eliminar el permiso "%s" porque está asociado a %s. Elimina primero esas asignaciones.',
            $permissionName,
            implode(' y ', $associations)
        );
    }
}