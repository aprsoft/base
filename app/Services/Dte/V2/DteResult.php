<?php

declare(strict_types=1);

namespace App\Services\Dte\V2;

/**
 * Outcome describes the provider request, NOT acceptance by the SII.
 *
 * emitted: OpenFactura reported successful emission.
 * retrieved: a lookup succeeded.
 * rejected: a definite provider rejection.
 * unknown: processing may have occurred; reconcile before retrying.
 *
 * data can contain sensitive XML/PDF and must not be logged or exposed publicly.
 */
final readonly class DteResult
{
    public function __construct(
        public string $outcome,
        public ?int $httpStatus = null,
        public ?int $folio = null,
        public ?string $token = null,
        public ?string $warning = null,
        public ?string $providerCode = null,
        public ?string $message = null,
        public array $data = [],
    ) {}

    public function emitted(): bool
    {
        return $this->outcome === 'emitted';
    }

    public function requiresReconciliation(): bool
    {
        return $this->outcome === 'unknown';
    }
}
