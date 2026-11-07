<?php

namespace App\Services\Document;

use App\Models\Document;
use App\Models\Detail;

class serviceFacturaAfecta
{
    public function createDocument(int $tipo)
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
            'IndExe'              => null,
            'service_id'          => 6,
            'QtyItem'             => 1,
            'PrcNetoItem'         => 2000,
            'PrcItem'             => 2380,
            'ivaItem'             => 380,
            'MontoItem'           => 2380,
            'detalle'             => 'servicio 1',
        ]);

        // Detail::create([
        //     'document_id'         => $document->id,
        //     'NroLinDet'           => 2,
        //     'IndExe'              => null,
        //     'service_id'          => 7,
        //     'QtyItem'             => 1,
        //     'PrcItem'             => 840,
        //     'ivaItem'             => 134,
        //     'MontoItem'           => 840,
        //     'detalle'             => 'servicio 2',
        // ]);

        return $document;
    }


}