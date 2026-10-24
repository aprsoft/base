<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Security\ProtectedSystemAccount;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users.index');
    }

    public function view(User $actor, User $target): bool
    {
        // La cuenta principal no puede consultarse
        // desde la administración de usuarios.
        if (ProtectedSystemAccount::isPrimary($target)) {
            return false;
        }

        // Las cuentas SuperAdmin solo pueden ser
        // consultadas por otro SuperAdmin.
        if ($target->hasRole('SuperAdmin', 'web')) {
            return $actor->hasRole('SuperAdmin', 'web');
        }

        return $actor->can('users.index');
    }

    public function create(User $actor): bool
    {
        return $actor->can('users.create');
    }

    public function update(User $actor, User $target): bool
    {
        // Nadie puede modificar al principal.
        if (ProtectedSystemAccount::isPrimary($target)) {
            return false;
        }

        // Ningún SuperAdmin puede editar sus
        // propios datos desde el módulo Usuarios.
        if (
            $actor->is($target)
            && $target->hasRole('SuperAdmin', 'web')
        ) {
            return false;
        }

        // Solo el principal puede modificar
        // nombre y correo de un SuperAdmin secundario.
        if ($target->hasRole('SuperAdmin', 'web')) {
            return ProtectedSystemAccount::isPrimary($actor)
                && $actor->hasRole('SuperAdmin', 'web');
        }

        // Administración ordinaria de usuarios.
        return $actor->hasRole('SuperAdmin', 'web')
            || $actor->can('users.edit');
    }

    public function delete(User $actor, User $target): bool
    {
        return $this->canManageLifecycle($actor, $target);
    }

    public function suspend(User $actor, User $target): bool
    {
        return $this->canManageLifecycle($actor, $target);
    }

    public function restore(User $actor, User $target): bool
    {
        return $this->canManageLifecycle($actor, $target);
    }

    public function forceDelete(
        User $actor,
        User $target
    ): bool {
        // No permitimos borrado físico desde este módulo.
        return false;
    }

    private function canManageLifecycle(
        User $actor,
        User $target
    ): bool {
        // Nadie puede suspender o eliminar
        // la cuenta principal.
        if (ProtectedSystemAccount::isPrimary($target)) {
            return false;
        }

        // Se prohíbe la suspensión o eliminación propia.
        if ($actor->is($target)) {
            return false;
        }

        // Un SuperAdmin puede administrar el ciclo
        // de vida de otro SuperAdmin secundario.
        if ($target->hasRole('SuperAdmin', 'web')) {
            return $actor->hasRole('SuperAdmin', 'web');
        }

        // Para usuarios normales se conserva el
        // permiso existente users.destroy.
        return $actor->hasRole('SuperAdmin', 'web')
            || $actor->can('users.destroy');
    }
}
