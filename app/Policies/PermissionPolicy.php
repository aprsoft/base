<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->allowed($actor, 'permissions.index');
    }

    public function create(User $actor): bool
    {
        return $this->allowed($actor, 'permissions.create');
    }

    public function view(User $actor, Permission $permission): bool
    {
        return $permission->guard_name === 'web'
            && $this->allowed($actor, 'permissions.show');
    }

    public function update(User $actor, Permission $permission): bool
    {
        return $permission->guard_name === 'web'
            && ! $this->isSystemPermission($permission)
            && $this->allowed($actor, 'permissions.edit')
            && ! $this->isRelated($permission, 'roles');
    }

    public function delete(User $actor, Permission $permission): bool
    {
        return $permission->guard_name === 'web'
            && ! $this->isSystemPermission($permission)
            && $this->allowed($actor, 'permissions.destroy')
            && ! $this->isRelated($permission, 'roles')
            && ! $this->isRelated($permission, 'users');
    }

    public function forceDelete(User $actor, Permission $permission): bool
    {
        return false;
    }

    /**
     * Comprueba si el permiso tiene relaciones.
     *
     * Aprovecha el resultado de withExists() cuando está
     * disponible para evitar consultas adicionales.
     *
     * Si el atributo no fue precargado, consulta la BD.
     */
    private function isRelated(
        Permission $permission,
        string $relation
    ): bool {
        $attribute = $relation.'_exists';

        if (array_key_exists($attribute, $permission->getAttributes())) {
            return (bool) $permission->getAttribute($attribute);
        }

        return $permission->{$relation}()->exists();
    }

    private function isSystemPermission(Permission $permission): bool
    {
        return str_starts_with($permission->name, 'users.')
            || str_starts_with($permission->name, 'roles.')
            || str_starts_with($permission->name, 'permissions.')
            || str_starts_with($permission->name, 'profiles.')
            || str_starts_with($permission->name, 'profile.')
            || str_starts_with($permission->name, 'errors.');
    }

    private function allowed(User $actor, string $permission): bool
    {
        return $actor->hasRole('SuperAdmin', 'web')
            || $actor->can($permission);
    }
}