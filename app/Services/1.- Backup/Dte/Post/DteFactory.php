<?php

namespace App\Services\Dte\Post;

use App\Models\Document;
use App\Services\Dte\Post\Mapper\BoletaExenta;
use App\Services\Dte\Post\Mapper\BoletaAfecta;
use App\Services\Dte\Post\Mapper\FacturaAfecta;
use App\Services\Dte\Post\Mapper\facturaExenta;
use App\Services\Dte\Post\Mapper\NotaCredito;
use App\Services\Dte\Post\Mapper\NotaDebito;

class DteFactory
{
    public static function resolve(Document $document)
    {
        return match ((int) $document->typedocument->codigo) {
            41 => new BoletaExenta($document),
            39 => new BoletaAfecta($document),
            33 => new FacturaAfecta($document),
            56 => new NotaDebito($document),
            61 => new NotaCredito($document),
            34 => new FacturaExenta($document),

          
            default => throw new \LogicException(
                'Tipo DTE no soportado por OpenFactura'
            ),
        };
    }
}


