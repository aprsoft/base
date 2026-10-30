<?php

declare(strict_types=1);

namespace App\Services\Dte\V2;

/**
 * Maps an application-owned source array to a provider-compatible DTE payload.
 * The caller supplies real business data; mappers must never choose companies.
 */
interface DteMapper
{
    public function type(): int;

    public function map(array $source): array;
}
