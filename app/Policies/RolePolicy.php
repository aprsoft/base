<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function update(User $actor, Role $role): bool
    {
        if ($this->isProtected($role)) {
            return false;
        }

        return $actor->hasRole('SuperAdmin', 'web');
    }

    public function delete(User $actor, Role $role): bool
    {
        if ($this->isProtected($role)) {
            return false;
        }

        return $actor->hasRole('SuperAdmin', 'web');
    }

    public function forceDelete(User $actor, Role $role): bool
    {
        return false;
    }

    private function isProtected(Role $role): bool
    {
        return $role->name === 'SuperAdmin'
            && $role->guard_name === 'web';
    }
}
