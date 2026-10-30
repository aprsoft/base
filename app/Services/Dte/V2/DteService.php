<?php

declare(strict_types=1);

namespace App\Services\Dte\V2;

use InvalidArgumentException;

/**
 * Service boundary for preassembled DTE data.
 * The caller MUST authorize the company/tenant and supply its own API key.
 * Does not persist, issue retries, calculate tax or declare SII acceptance.
 */
final readonly class DteService
{
    private const RESOURCES = [
        'XML', 'PDF', 'TIMBRE', 'LOGO', 'FOLIO', 'RESOLUCION',
        'LETTER', '80MM', '80MMNOLOGO', 'LETTER_80MM',
    ];

    public function __construct(
        private DteDocumentValidator $validator,
        private OpenFacturaClient $client,
    ) {}

    /**
     * $idempotencyKey must be persisted/reused by the caller per issuer and operation.
     *
     * @param array<string, mixed> $dte
     * @param list<string> $resources
     */
    public function emit(
        array $dte,
        string $apiKey,
        string $idempotencyKey,
        string $environment = 'development',
        array $resources = ['FOLIO', 'XML'],
    ): DteResult {
        $this->validator->validate($dte);

        if ($resources === []) {
            throw new InvalidArgumentException('Debe solicitar al menos un recurso de respuesta.');
        }

        foreach ($resources as $resource) {
            if (!is_string($resource) || !in_array($resource, self::RESOURCES, true)) {
                throw new InvalidArgumentException('Recurso de respuesta OpenFactura inválido.');
            }
        }

        return $this->client->emit(
            payload: [
                'response' => array_values(array_unique($resources)),
                'dte' => $dte,
            ],
            apiKey: $apiKey,
            idempotencyKey: $idempotencyKey,
            environment: $environment,
        );
    }

    public function get(
        string $rut,
        int $type,
        int $folio,
        string $format,
        string $apiKey,
        string $environment = 'development',
    ): DteResult {
        return $this->client->getDocument(
            rut: $rut,
            type: $type,
            folio: $folio,
            format: $format,
            apiKey: $apiKey,
            environment: $environment,
        );
    }
}
