<?php

namespace App\Services\Document;

use App\Services\Document\ConsumptionDocumentService;
use App\Services\Document\ServiceDocumentService;

class DocumentServiceFactory
{
    public static function make(array $data)
    {
        //dd($data);
        if (!empty($data['lectura']) && !empty($data['meter_id']) && !empty($data['customer_id'])) {
            return new ConsumptionDocumentService();
        }

        if (!empty($data['services']) && !empty($data['meter_id']) && !empty($data['customer_id']) && empty($data['reading_id'])) {
            return new ServiceDocumentService();
        }

        throw new \Exception('No se pudo determinar el tipo de documento.');
    }
}
