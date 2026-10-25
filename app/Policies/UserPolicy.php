<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->hasAccess($actor, 'users.index');
    }

    public function view(User $actor, User $target): bool
    {
        if ($this->isPrimary($target)) {
            return false;
        }

        return $this->mayManageTarget($actor, $target, 'users.index');
    }

    public function create(User $actor): bool
    {
        return $this->hasAccess($actor, 'users.create');
    }

    public function update(User $actor, User $target): bool
    {
        if ($this->isPrimary($target) || $target->trashed() || $actor->is($target)) {
            return false;
        }

        return $this->mayManageTarget($actor, $target, 'users.edit');
    }

    public function suspend(User $actor, User $target): bool
    {
        if ($this->isPrimary($target) || $target->trashed() || $actor->is($target)) {
            return false;
        }

        return $this->mayManageTarget($actor, $target, 'users.suspend');
    }

    public function restore(User $actor, User $target): bool
    {
        if ($this->isPrimary($target) || ! $target->trashed() || $actor->is($target)) {
            return false;
        }

        return $this->mayManageTarget($actor, $target, 'users.restore');
    }

    // Reservada para la eliminación definitiva, una vez auditadas las relaciones.
    // Hasta entonces no se permite el borrado físico de cuentas.
    public function delete(User $actor, User $target): bool
    {
        return false;
    }

    public function forceDelete(User $actor, User $target): bool
    {
        return false;
    }

    private function mayManageTarget(User $actor, User $target, string $permission): bool
    {
        if ($target->hasRole('SuperAdmin', 'web') && ! $actor->hasRole('SuperAdmin', 'web')) {
            return false;
        }

        return $this->hasAccess($actor, $permission);
    }

    private function hasAccess(User $actor, string $permission): bool
    {
        return $actor->hasRole('SuperAdmin', 'web') || $actor->can($permission);
    }

    private function isPrimary(User $target): bool
    {
        // Protección confirmada en los requisitos de BASE.
        return (int) $target->getKey() === 1;
    }
}
