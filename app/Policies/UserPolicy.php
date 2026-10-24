<?php

namespace App\Policies;

use App\Models\User;

/**
 * Autorizaciones de consulta y edición de usuarios.
 *
 * La cuenta principal no puede ser administrada por ningún SuperAdmin.
 * Las demás cuentas SuperAdmin mantienen los privilegios del rol.
 */
class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users.index');
    }

    public function view(User $actor, User $target): bool
    {
        return $this->mayAccess($actor, $target, 'users.index');
    }

    public function create(User $actor): bool
    {
        return $actor->can('users.create');
    }

    public function update(User $actor, User $target): bool
    {
        return $this->mayAccess($actor, $target, 'users.edit');
    }

    private function mayAccess(User $actor, User $target, string $permission): bool
    {
        if ($target->isPrimarySuperAdmin() || ! $actor->can($permission)) {
            return false;
        }

        if ($target->hasRole('SuperAdmin', 'web')) {
            return $actor->hasRole('SuperAdmin', 'web');
        }

        return true;
    }
}
