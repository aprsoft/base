<?php

namespace App\Policies;

use App\Models\User;

class ProfilePolicy
{
    public function view(User $actor): bool
    {
        return true;
    }

    public function update(User $actor): bool
    {
        // La cuenta principal no modifica su nombre/correo desde Settings.
        return (int) $actor->getKey() !== 1;
    }

    /* Autoeliminación deshabilitada en BASE.
    public function delete(User $actor): bool
    {
        // Ningún SuperAdmin puede eliminar su propia cuenta.
        return (int) $actor->getKey() !== 1
            && ! $actor->hasRole('SuperAdmin', 'web');
    }
    */

    public function changePassword(User $actor): bool
    {
        return true;
    }
}
