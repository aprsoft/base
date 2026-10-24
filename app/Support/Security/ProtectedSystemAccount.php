<?php

namespace App\Support\Security;

use App\Models\User;

final class ProtectedSystemAccount
{
    // Identidad actualmente definida en UserSeeder.
    public const PRIMARY_EMAIL = 'superadmin@aprsoft.cl';

    public static function isPrimary(User $user): bool
    {
        return strcasecmp(
            (string) $user->getRawOriginal('email'),
            self::PRIMARY_EMAIL
        ) === 0;
    }
}
