<?php

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
            && $this->allowed($actor, 'permissions.edit');
    }

    public function delete(User $actor, Permission $permission): bool
    {
        return $permission->guard_name === 'web'
            && $this->allowed($actor, 'permissions.destroy');
    }

    public function forceDelete(User $actor, Permission $permission): bool
    {
        return false;
    }

    private function allowed(User $actor, string $permission): bool
    {
        return $actor->hasRole('SuperAdmin', 'web') || $actor->can($permission);
    }
}
