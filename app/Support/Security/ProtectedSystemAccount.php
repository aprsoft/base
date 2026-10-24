<?php

namespace App\Support\Security;

use App\Models\User;

final class ProtectedSystemAccount
{
    /**
     * Correo usado por instalaciones anteriores.
     *
     * Compatibilidad temporal durante la transición.
     */
    private const LEGACY_PRIMARY_EMAIL = 'superadmin@aprsoft.cl';

    /**
     * Identifica la cuenta principal protegida.
     */
    public static function isPrimary(User $user): bool
    {
        // Identificación permanente mediante la nueva columna.
        if ($user->isPrimarySuperAdmin()) {
            return true;
        }

        /*
         * Compatibilidad con instalaciones existentes.
         *
         * Hasta verificar y marcar la cuenta principal,
         * conservamos la protección anterior.
         *
         * Este bloque podrá retirarse una vez completada
         * la identificación de todas las instalaciones.
         */
        return $user->system_account_key === null
            && strcasecmp(
                (string) $user->getRawOriginal('email'),
                self::LEGACY_PRIMARY_EMAIL
            ) === 0;
    }
}
