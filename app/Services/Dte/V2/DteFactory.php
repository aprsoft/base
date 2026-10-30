<?php

declare(strict_types=1);

namespace App\Services\Dte\V2;

use App\Services\Dte\V2\Mapper\BoletaAfecta;
use App\Services\Dte\V2\Mapper\BoletaExenta;
use App\Services\Dte\V2\Mapper\FacturaAfecta;
use App\Services\Dte\V2\Mapper\FacturaExenta;
use App\Services\Dte\V2\Mapper\GuiaDespacho;
use App\Services\Dte\V2\Mapper\NotaCredito;
use App\Services\Dte\V2\Mapper\NotaDebito;
use InvalidArgumentException;

final class DteFactory
{
    public function resolve(int $type): DteMapper
    {
        return match ($type) {
            33 => new FacturaAfecta(),
            34 => new FacturaExenta(),
            39 => new BoletaAfecta(),
            41 => new BoletaExenta(),
            52 => new GuiaDespacho(),
            56 => new NotaDebito(),
            61 => new NotaCredito(),
            default => throw new InvalidArgumentException('Tipo DTE no soportado.'),
        };
    }
}
