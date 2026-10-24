<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Security\ProtectedSystemAccount;

class ProfilePolicy
{
    public function view(User $actor, User $target): bool
    {
        return $this->canManageOwnProfile(
            $actor,
            $target,
            'profile.view'
        );
    }

    public function update(User $actor, User $target): bool
    {
        return $this->canManageOwnProfile(
            $actor,
            $target,
            'profile.update'
        );
    }

    public function delete(User $actor, User $target): bool
    {
        return $this->canManageOwnProfile(
            $actor,
            $target,
            'profile.delete'
        );
    }

    public function changePassword(
        User $actor,
        User $target
    ): bool {
        // Solo se permite cambiar la propia contraseña.
        if (! $actor->is($target)) {
            return false;
        }

        // Todos los SuperAdmin, principal y secundarios,
        // pueden cambiar su propia contraseña.
        if ($actor->hasRole('SuperAdmin', 'web')) {
            return true;
        }

        // Los demás usuarios requieren el permiso.
        return $actor->hasPermissionTo(
            'profile.password.update',
            'web'
        );
    }

    private function canManageOwnProfile(
        User $actor,
        User $target,
        string $permission
    ): bool {
        if (! $actor->is($target)) {
            return false;
        }

        // Prohibido editar o eliminar el perfil
        // del SuperAdministrador principal.
        if (ProtectedSystemAccount::isPrimary($target)) {
            return false;
        }

        // Todos los SuperAdmin secundarios tienen
        // las mismas restricciones de perfil propio.
        if ($actor->hasRole('SuperAdmin', 'web')) {
            return false;
        }

        // Usuarios normales: permisos asignados.
        return $actor->hasPermissionTo(
            $permission,
            'web'
        );
    }
}
