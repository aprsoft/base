<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Security\ProtectedSystemAccount;

class ProfilePolicy
{
    public function view(User $actor, User $target): bool
    {
        return $this->canManage(
            $actor,
            $target,
            'profile.view'
        );
    }

    public function update(User $actor, User $target): bool
    {
        return $this->canManage(
            $actor,
            $target,
            'profile.update'
        );
    }

    public function updatePassword(User $actor, User $target): bool 
    {
        if (! $actor->is($target)) {
            return false;
        }

        if (ProtectedSystemAccount::isPrimary($target)) {
            return true;
        }

        return $actor->hasRole('SuperAdmin', 'web')
            || $actor->hasPermissionTo(
                'profile.password.update',
                'web'
            );
    }

    public function delete(User $actor, User $target): bool
    {
        return $this->canManage(
            $actor,
            $target,
            'profile.delete'
        );
    }

    public function changePassword(
        User $actor,
        User $target
    ): bool {
        // Cambiar la contraseña propia siempre está permitido.
        return $actor->is($target);
    }

    private function canManage(
        User $actor,
        User $target,
        string $permission
    ): bool {
        if (! $actor->is($target)) {
            return false;
        }

        if (ProtectedSystemAccount::isPrimary($target)) {
            return false;
        }

        // SuperAdmin adicional conserva sus atribuciones.
        if ($actor->hasRole('SuperAdmin', 'web')) {
            return true;
        }

        return $actor->hasPermissionTo($permission, 'web');
    }
}
