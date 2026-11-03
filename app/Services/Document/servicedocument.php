<?php

namespace App\Services\Document;

use App\Models\Document;
use App\Models\Detail;

class servicedocument
{
    public function createDocument($tipo)
    {
        $document = Document::create([
            'parent_id'           => null,
            'user_id'             => 1,
            'customer_id'         => 1,
            'supplier_id'         => null,
            'meter_id'            => null,
            'typedocument_id'     => $tipo,
            'fecha'               => now()->toDateString(),
           
        ]);

        Detail::create([
            'document_id'         => $document->id,
            'NroLinDet'           => 1,
            'service_id'          => 6,
            'cantidad'            => 2,
            'valor_exento'        => 0,
            'valor_neto'          => 840,
            'valor'               => 1000,
            'neto'                => 1681,
            'iva'                 => 319,
            'total'               => 2000,
            'detalle'             => 'servicio 1',
        ]);

         Detail::create([
            'document_id'         => $document->id,
            'NroLinDet'           => 2,
            'service_id'          => 7,
            'cantidad'            => 1,
            'valor_exento'        => 840,
            'valor_neto'          => 0,
            'valor'               => 0,
            'neto'                => 0,
            'iva'                 => 0,
            'total'               => 840,
            'detalle'             => 'servicio 2',
        ]);

        return $document;
    }


}