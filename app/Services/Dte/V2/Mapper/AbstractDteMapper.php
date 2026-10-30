<?php

declare(strict_types=1);

namespace App\Services\Dte\V2\Mapper;

use App\Services\Dte\V2\DteMapper;
use InvalidArgumentException;

/**
 * Accepts DTE arrays in the exact official SII/OpenFactura naming convention.
 * Does not silently rewrite values, adjust IVA, or fill defaults.
 */
abstract class AbstractDteMapper implements DteMapper
{
    final public function map(array $source): array
    {
        $type = $source['Encabezado']['IdDoc']['TipoDTE'] ?? null;
        if ($type !== $this->type()) {
            throw new InvalidArgumentException('TipoDTE no corresponde al mapper seleccionado.');
        }

        $this->validateSpecific($source);

        return $source;
    }

    protected function validateSpecific(array $source): void
    {
    }

    protected function requireReferences(array $source): void
    {
        $references = $source['Referencia'] ?? null;
        if (!is_array($references) || !array_is_list($references) || $references === []) {
            throw new InvalidArgumentException('Se requieren referencias tributarias.');
        }
    }
}
