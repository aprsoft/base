<?php

declare(strict_types=1);

namespace App\Services\Dte\V2;

use InvalidArgumentException;

/**
 * Explicit-boundary service. Callers must verify permissions and company/tenant
 * ownership, then persist key/result/XML before exposing actions to users.
 */
final readonly class DteService
{
    private const RESOURCES = [
        'TIMBRE', 'FOLIO', 'XML', 'PDF', 'LOGO', 'RESOLUCION',
        '80MM', 'LETTER', '80MMNOLOGO', 'LETTER_80MM',
    ];

    public function __construct(
        private DteDocumentValidator $validator,
        private DteFactory $factory,
        private OpenFacturaClient $client,
    ) {}

    public function emit(
        array $dte,
        string $apiKey,
        string $idempotencyKey,
        string $environment = 'development',
        array $resources = ['FOLIO', 'XML'],
    ): DteResult {
        $this->validator->validate($dte);
        $type = $dte['Encabezado']['IdDoc']['TipoDTE'];
        $mapped = $this->factory->resolve($type)->map($dte);
        if ($resources === [] || !array_is_list($resources)) {
            throw new InvalidArgumentException('Debe solicitar una lista de recursos.');
        }
        foreach ($resources as $resource) {
            if (!is_string($resource) || !in_array($resource, self::RESOURCES, true)) {
                throw new InvalidArgumentException('Recurso OpenFactura no permitido.');
            }
        }

        return $this->client->emit([
            'response' => array_values(array_unique($resources)),
            'dte' => $mapped,
        ], $apiKey, $idempotencyKey, $environment);
    }

    public function get(
        string $rut,
        int $type,
        int $folio,
        string $format,
        string $apiKey,
        string $environment = 'development',
    ): DteResult {
        return $this->client->document($rut, $type, $folio, $format, $apiKey, $environment);
    }

    public function issued(array $filters, string $apiKey, string $environment = 'development'): DteResult
    {
        return $this->client->listDocuments('issued', $filters, $apiKey, $environment);
    }

    public function received(array $filters, string $apiKey, string $environment = 'development'): DteResult
    {
        return $this->client->listDocuments('received', $filters, $apiKey, $environment);
    }

    public function organization(string $apiKey, string $environment = 'development'): DteResult
    {
        return $this->client->organization($apiKey, $environment);
    }
}
