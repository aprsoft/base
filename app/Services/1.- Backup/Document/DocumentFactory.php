<?php

namespace App\Services\Document;

use App\Services\Document\DocumentType\ConsumptionDocument;
use App\Services\Document\DocumentType\ServiceDocument;

class DocumentFactory
{
    public static function resolve($request)
    {
        if ($request->has(['lectura_nueva', 'meter_id']) &&
            $request->only(['lectura_nueva', 'meter_id'])->every(fn ($v) => filled($v))
        ) {
            return new ConsumptionDocument($request);
        }

        if ($request->has(['services', 'meter_id', 'customer_id']) &&
            filled($request->get('services')) &&
            blank($request->get('reading_id'))
        ) {
            return new ServiceDocument();
        }

        throw new \Exception('No se pudo determinar el tipo de documento.');
    }
}
