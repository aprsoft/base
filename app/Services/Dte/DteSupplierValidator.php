<?php

namespace App\Services\Dte;

use InvalidArgumentException;

class DteSupplierValidator
{
    public static function validate(array $data): void
    {
        if (!isset($data['Encabezado'])) {
            throw new InvalidArgumentException('DTE sin Encabezado');
        }

        if (!isset($data['Encabezado']['IdDoc']['TipoDTE'])) {
            throw new InvalidArgumentException('TipoDTE no informado');
        }

        if (!isset($data['Encabezado']['IdDoc']['Folio'])) {
            throw new InvalidArgumentException('Folio no informado');
        }

        if (!isset($data['Encabezado']['Emisor']['RUTEmisor'])) {
            throw new InvalidArgumentException('RUT Emisor no informado');
        }

        if (!isset($data['Encabezado']['Totales']['MntTotal'])) {
            throw new InvalidArgumentException('Monto total no informado');
        }
    }
}
