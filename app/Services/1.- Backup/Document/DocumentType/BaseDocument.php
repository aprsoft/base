<?php

namespace App\Services\Document\DocumentType;

use App\Models\Document;
use App\Models\Meter;


abstract class BaseDocument
{    
    protected function createDocumentBase($data): Document
    {
        $meterId = $data->get('meter_id');

        $meter = $meterId
            ? Meter::find($meterId)
            : null;

        $customer_id = optional($meter?->customer->first())->id;
        $meter_id    = $meter?->id;

        return Document::create([
            'parent_id'           => $data->get('parent_id'),
            'user_id'             => $data->get('user_id'),
            'customer_id'         => $customer_id,
            'supplier_id'         => $data->get('supplier_id'),
            'meter_id'            => $meter_id,
            'reading_id'          => $data->get('reading_id'),
            'estado_documento'    => 'VIGENTE',
            'condicion_documento' => 'POR DEFINIR',
            'fecha'               => $data->get('fecha') ?? now()->toDateString(),
            // 'monto_exento'        => $data->get('monto_exento'),
            // 'monto_neto'          => $data->get('monto_neto'),
            // 'monto_iva'           => $data->get('monto_iva'),
            // 'monto_total'         => $data->get('monto_total'),
        ]);
    }

    /**
     * Update SIN transacción
     */
    protected function updateDocument(int $id, array $data): Document
    {
        $document = Document::findOrFail($id);
        $document->update($data);

        return $document;
    }

    /**
     * Soft delete SIN transacción
     */
    protected function softDeleteDocument(int $id): bool
    {
        $document = Document::findOrFail($id);
        return (bool) $document->delete();
    }
}
