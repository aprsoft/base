<?php

namespace App\Services\Document;

use App\Models\Document;
use App\Models\Detail;

class serviceNotaCredito
{
    public function createDocument(Document $document): Document
    {
        $objDocument = Document::create([
            'parent_id'           => $document->id,
            'user_id'             => 1,
            'customer_id'         => $document->customer_id,
            'supplier_id'         => null,
            'meter_id'            => null,
            'typedocument_id'     => 13,
            'fecha'               => now()->toDateString(),
            'CodRef'              => 1,
           
        ]);

        foreach($document->details as  $detail) {

                $objDetail=Detail::create([
                    'document_id'=>$document->id,
                    'estado_detalle'=>'VIGENTE',
                    'service_id'=>$detail->service_id,
                    'QtyItem'=>$detail->QtyItem,
                    'PrcNetoItem'=> $detail->valPrcNetoItemor,
                    'MontoNetoItem'=>$detail->MontoNetoItem,
                    'ivaItem'=>$detail->ivaItem,
                    'MontoItem'=>$detail->neto,
                    'detalle'=>$detail->detail
                ]);
            }

        return $objDocument;
    }


}