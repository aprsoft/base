<?php

declare(strict_types=1);

namespace App\Services\Dte\V2;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * HTTP transport only. No DB, tenant selection, document mapping or automatic
 * emission retries. Credentials must belong to the already-authorized issuer.
 */
final class OpenFacturaClient
{
    private const BASE_URLS = [
        'development' => 'https://dev-api.haulmer.com',
        'production' => 'https://api.haulmer.com',
    ];

    private const FORMATS = ['status', 'xml', 'json', 'pdf', 'cedible'];

    public function emit(
        array $payload,
        string $apiKey,
        string $idempotencyKey,
        string $environment = 'development',
    ): DteResult {
        $this->assertKey($apiKey);
        if (!preg_match('/\A[a-zA-Z0-9:_-]{1,200}\z/D', $idempotencyKey)) {
            throw new InvalidArgumentException('Clave de idempotencia no válida.');
        }

        try {
            $response = $this->request($apiKey)
                ->withHeaders(['Idempotency-Key' => $idempotencyKey])
                ->post($this->baseUrl($environment).'/v2/dte/document', $payload);
        } catch (ConnectionException) {
            return new DteResult(
                outcome: 'unknown',
                message: 'No se conoce el resultado de la emisión. Conciliar con OpenFactura antes de reintentar.',
            );
        }

        return $this->interpret($response, issuance: true);
    }

    public function document(
        string $rut,
        int $type,
        int $folio,
        string $format,
        string $apiKey,
        string $environment = 'development',
    ): DteResult {
        $this->assertKey($apiKey);
        if (!preg_match('/\A[0-9]{7,8}-[0-9Kk]\z/D', $rut)
            || !in_array($type, [33, 34, 39, 41, 52, 56, 61], true)
            || $folio <= 0
            || !in_array($format, self::FORMATS, true)) {
            throw new InvalidArgumentException('Parámetros de consulta DTE inválidos.');
        }

        try {
            $response = $this->request($apiKey)->get(
                $this->baseUrl($environment)."/v2/dte/document/{$rut}/{$type}/{$folio}/{$format}"
            );
        } catch (ConnectionException) {
            return new DteResult(outcome: 'unknown', message: 'No fue posible consultar el DTE.');
        }

        return $this->interpret($response, issuance: false);
    }

    public function listDocuments(
        string $direction,
        array $filters,
        string $apiKey,
        string $environment = 'development',
    ): DteResult {
        $this->assertKey($apiKey);
        if (!in_array($direction, ['issued', 'received'], true)) {
            throw new InvalidArgumentException('Dirección de listado no válida.');
        }
        $this->assertFilters($filters, $direction);

        try {
            $response = $this->request($apiKey)->post(
                $this->baseUrl($environment).'/v2/dte/document/'.$direction,
                $filters
            );
        } catch (ConnectionException) {
            return new DteResult(outcome: 'unknown', message: 'No fue posible consultar el listado de DTE.');
        }

        return $this->interpret($response, issuance: false);
    }

    public function organization(string $apiKey, string $environment = 'development'): DteResult
    {
        $this->assertKey($apiKey);
        try {
            $response = $this->request($apiKey)->get(
                $this->baseUrl($environment).'/v2/dte/organization'
            );
        } catch (ConnectionException) {
            return new DteResult(outcome: 'unknown', message: 'No fue posible consultar el contribuyente.');
        }

        return $this->interpret($response, issuance: false);
    }

    private function request(string $apiKey): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders(['apikey' => $apiKey])
            ->acceptJson()
            ->asJson()
            ->withoutRedirecting()
            ->connectTimeout(5)
            ->timeout(30);
    }

    private function baseUrl(string $environment): string
    {
        return self::BASE_URLS[$environment]
            ?? throw new InvalidArgumentException('Ambiente OpenFactura no permitido.');
    }

    private function assertKey(string $apiKey): void
    {
        if (trim($apiKey) === '') {
            throw new InvalidArgumentException('Falta API Key del contribuyente autorizado.');
        }
    }

    private function assertFilters(array $filters, string $direction): void
    {
        $allowed = $direction === 'issued'
            ? ['TipoDTE', 'FchEmis', 'RUTRecep']
            : ['TipoDTE', 'FchEmis', 'RUTEmisor', 'FchRecepOF', 'FchRecepSII'];

        if (!isset($filters['Page']) || !ctype_digit((string) $filters['Page'])
            || (int) $filters['Page'] < 1) {
            throw new InvalidArgumentException('Page debe ser un entero positivo.');
        }
        foreach ($filters as $field => $filter) {
            if ($field === 'Page') {
                continue;
            }
            if (!in_array($field, $allowed, true) || !is_array($filter) || $filter === []) {
                throw new InvalidArgumentException('Filtro no permitido o mal formado: '.$field);
            }
            foreach ($filter as $operator => $value) {
                if (!in_array($operator, ['eq', 'lt', 'gt', 'lte', 'gte', 'ne'], true)
                    || (!is_string($value) && !is_int($value))) {
                    throw new InvalidArgumentException('Operador o valor de filtro no permitido.');
                }
            }
        }
    }

    private function interpret(Response $response, bool $issuance): DteResult
    {
        $data = $response->json();

        if (!is_array($data)) {
            return new DteResult(
                outcome: 'unknown',
                httpStatus: $response->status(),
                message: 'Respuesta inesperada del proveedor. No reintentar emisión sin conciliar.',
            );
        }

        $error = is_array($data['error'] ?? null) ? $data['error'] : null;
        $code = is_string($error['code'] ?? null) ? $error['code'] : null;
        $message = is_string($error['message'] ?? null)
            ? $error['message']
            : (is_string($data['message'] ?? null) ? $data['message'] : null);

        $token = is_string($data['TOKEN'] ?? null) ? $data['TOKEN'] : null;
        $warning = is_string($data['WARNING'] ?? null) ? $data['WARNING'] : null;
        $folioValue = $data['FOLIO'] ?? $data['folio'] ?? null;
        $folio = is_int($folioValue)
            ? $folioValue
            : ((is_string($folioValue) && ctype_digit($folioValue)) ? (int) $folioValue : null);

        if ($response->successful() && $error === null) {
            if ($issuance && ($token === null || $token === '')) {
                return new DteResult(
                    outcome: 'unknown', httpStatus: $response->status(), folio: $folio,
                    warning: $warning, message: 'Emisión sin TOKEN; requiere conciliación.',
                    data: $data,
                );
            }
            return new DteResult(
                outcome: $issuance ? 'emitted' : 'retrieved',
                httpStatus: $response->status(), folio: $folio,
                token: $token, warning: $warning, data: $data,
            );
        }

        // OF-06 can identify an earlier emission, possibly including its TOKEN.
        // Neither that token nor a 5xx guarantees the document's final SII state.
        $uncertain = $code === 'OF-06'
            || $response->serverError()
            || in_array($response->status(), [408, 429], true);

        return new DteResult(
            outcome: $uncertain ? 'unknown' : 'rejected',
            httpStatus: $response->status(),
            folio: $folio,
            token: $token,
            warning: $warning,
            providerCode: $code,
            message: $message ?? 'Error de OpenFactura.',
            data: $data,
        );
    }
}
