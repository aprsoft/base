<?php

declare(strict_types=1);

namespace App\Services\Dte\V2\Mapper;

use InvalidArgumentException;

final class GuiaDespacho extends AbstractDteMapper
{
    public function type(): int
    {
        return 52;
    }

    protected function validateSpecific(array $source): void
    {
        if (!isset($source['Encabezado']['IdDoc']['IndTraslado'])) {
            throw new InvalidArgumentException('Guía de despacho requiere IndTraslado.');
        }
    }
}
