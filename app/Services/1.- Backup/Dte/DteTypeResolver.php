<?php

namespace App\Services\Dte;

class DteTypeResolver
{
    public static function isExento(int $tipoDte): bool
    {
        return in_array($tipoDte, [34,41], true);
    }

    public static function isAfecto(int $tipoDte): bool
    {
        return in_array($tipoDte, [33,39], true);
    }

    public static function isNotaCredito(int $tipoDte): bool
    {
        return in_array($tipoDte, [61], true);
    }

    public static function isNotaDebito(int $tipoDte): bool
    {
        return in_array($tipoDte, [56], true);
    }
}
