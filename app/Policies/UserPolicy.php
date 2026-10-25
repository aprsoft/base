<?php

namespace App\Policies;

use App\Models\User;

/**
 * Autorizaciones de consulta y edición de usuarios.
 *
 * Los usuarios que no sean SuperAdmin no pueden acceder a cuentas
 * que tengan ese rol ni a la cuenta principal (ID 1).
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
        if (! $actor->can($permission)) {
            return false;
        }

        if ((int) $target->getKey() === 1 || $target->hasRole('SuperAdmin', 'web')) {
            return $actor->hasRole('SuperAdmin', 'web');
        }

        return true;
    }
}
