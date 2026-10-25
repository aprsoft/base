<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->isAdministrator($actor);
    }

    public function create(User $actor): bool
    {
        return $this->isAdministrator($actor);
    }

    public function view(User $actor, Role $role): bool
    {
        return $this->isAdministrator($actor) && $this->mayAccess($role);
    }

    public function update(User $actor, Role $role): bool
    {
        return $this->isAdministrator($actor) && $this->mayAccess($role);
    }

    public function delete(User $actor, Role $role): bool
    {
        return $this->isAdministrator($actor) && $this->mayAccess($role);
    }

    public function forceDelete(User $actor, Role $role): bool
    {
        return false;
    }

    private function isAdministrator(User $actor): bool
    {
        return $actor->hasRole('SuperAdmin', 'web');
    }

    private function mayAccess(Role $role): bool
    {
        return $role->guard_name === 'web' && $role->name !== 'SuperAdmin';
    }
}
