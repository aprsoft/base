<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->allowed($actor, 'roles.index');
    }

    public function create(User $actor): bool
    {
        return $this->allowed($actor, 'roles.create');
    }

    public function view(User $actor, Role $role): bool
    {
        return $this->isManageable($role) && $this->allowed($actor, 'roles.show');
    }

    public function update(User $actor, Role $role): bool
    {
        return $this->isManageable($role) && $this->allowed($actor, 'roles.edit');
    }

    public function delete(User $actor, Role $role): bool
    {
        return $this->isManageable($role) && $this->allowed($actor, 'roles.destroy');
    }

    public function forceDelete(User $actor, Role $role): bool
    {
        return false;
    }

    private function isManageable(Role $role): bool
    {
        return $role->guard_name === 'web' && $role->name !== 'SuperAdmin';
    }

    private function allowed(User $actor, string $permission): bool
    {
        return $actor->hasRole('SuperAdmin', 'web') || $actor->can($permission);
    }
}
