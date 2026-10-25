<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->isAdministrator($actor);
    }

    public function create(User $actor): bool
    {
        return $this->isAdministrator($actor);
    }

    public function view(User $actor, Permission $permission): bool
    {
        return $this->canManage($actor, $permission);
    }

    public function update(User $actor, Permission $permission): bool
    {
        return $this->canManage($actor, $permission);
    }

    public function delete(User $actor, Permission $permission): bool
    {
        // El servicio existente sigue verificando asignaciones activas.
        return $this->canManage($actor, $permission);
    }

    public function forceDelete(User $actor, Permission $permission): bool
    {
        return false;
    }

    private function isAdministrator(User $actor): bool
    {
        return $actor->hasRole('SuperAdmin', 'web');
    }

    private function canManage(User $actor, Permission $permission): bool
    {
        return $this->isAdministrator($actor) && $permission->guard_name === 'web';
    }
}
