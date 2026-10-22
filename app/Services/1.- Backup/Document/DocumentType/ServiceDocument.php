<?php

namespace App\Services\Document\DocumentType;

use App\Models\Document;
use App\Services\Document\DocumentType\BaseDocument;
use Illuminate\Support\Facades\DB;

class ServiceDocument extends BaseDocument
{
    public function store(array $data): Document
    {
        return DB::transaction(function () use ($data) {

            // 1️⃣ Crear documento base
            $document = parent::createDocumentBase($data);

            // 2️⃣ Crear detalles de servicios
            $this->createServiceDetails($document, $data);

            // 3️⃣ Validar que se hayan creado detalles
            if ($document->details()->count() === 0) {
                throw new \Exception('No se pudieron crear los detalles de servicios.');
            }

            return $document;
        });
    }

    protected function createServiceDetails(Document $document, array $data)
    {
        if (!empty($data['services']) && is_array($data['services'])) {
            foreach ($data['services'] as $service) {
                $document->details()->create([
                    'description' => $service['description'] ?? null,
                    'amount'      => $service['amount'] ?? 0,
                ]);
            }
        }
    }
}
