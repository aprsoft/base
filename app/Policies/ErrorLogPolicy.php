<?php

namespace App\Policies;

use App\Models\ErrorLog;
use App\Models\User;

class ErrorLogPolicy
{
    public function viewAny(User $actor): bool
    {
        return $this->allowed($actor, 'errors.index');
    }

    public function view(User $actor, ErrorLog $errorLog): bool
    {
        return $this->allowed($actor, 'errors.show');
    }

    public function create(User $actor): bool
    {
        return false;
    }

    public function update(User $actor, ErrorLog $errorLog): bool
    {
        return false;
    }

    public function delete(User $actor, ErrorLog $errorLog): bool
    {
        return false;
    }

    private function allowed(User $actor, string $permission): bool
    {
        return $actor->hasRole('SuperAdmin', 'web') || $actor->can($permission);
    }
}
