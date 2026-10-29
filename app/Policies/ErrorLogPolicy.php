<?php

namespace App\Policies;

use App\Models\User;

class ErrorLogPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->allowed($actor, 'errors.index');
    }

    public function view(User $actor): bool
    {
        return $this->allowed($actor, 'errors.show');
    }


    public function delete(User $actor): bool
    {
        return false;
    }

    private function allowed(User $actor, string $permission): bool
    {
        return $actor->hasRole('SuperAdmin', 'web') || $actor->can($permission);
    }
}
