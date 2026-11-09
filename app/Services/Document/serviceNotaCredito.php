<?php

namespace App\Services\Document;

use App\Models\Document;
use App\Models\Detail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class serviceNotaCredito
{
    public function createDocument(Document $document, int $CodRef): Document
    {
        DB::beginTransaction();

        try {

        // Código	Descripción del SII	                    Uso
        // ----------------------------------------------------------------------------------------------------
        // 1	    Anula Documento de Referencia	        Anulación total de una factura
        // 2	    Corrige Texto Documento de Referencia	Corregir errores de escritura
        // 3	    Corrige montos	                        Corrección de valores, incluyendo rebajas parciales

            $objDocument = Document::create([
                'parent_id'       => $document->id,
                'user_id'         => 1,
                'customer_id'     => $document->customer_id,
                'supplier_id'     => null,
                'meter_id'        => null,
                'typedocument_id' => 13,
                'fecha'           => now()->toDateString(),
                'CodRef'          => $CodRef,
            ]);

            foreach ($document->details as $detail) {

                Detail::create([
                    'document_id'      => $objDocument->id,
                    'estado_detalle'   => 'VIGENTE',
                    'service_id'       => $detail->service_id,
                    'QtyItem'          => $detail->QtyItem,
                    'PrcNetoItem'      => $detail->PrcNetoItem,
                    'PrcItem'          => $detail->PrcItem,
                    'MontoNetoItem'    => $detail->MontoNetoItem,
                    'ivaItem'          => $detail->ivaItem,
                    'MontoItem'        => $detail->MontoItem,
                    'detalle'          => $detail->detalle,
                ]);
            }

            DB::commit();

            return $objDocument;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Error al crear nota de crédito', [
                'parent_id' => $document->id,
                'error'     => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}