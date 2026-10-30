<?php

declare(strict_types=1);

namespace App\Services\Dte\V2;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

final class OpenFacturaClient
{
    private const ENDPOINTS = [
        'development' => 'https://dev-api.haulmer.com/v2/dte/document',
        'production' => 'https://api.haulmer.com/v2/dte/document',
    ];

    private const FORMATS = ['json', 'pdf', 'xml', 'status', 'folio', 'cedible'];

    /**
     * There are deliberately NO automatic retries of POST emission.
     * A timeout is an indeterminate outcome, not proof that issuance failed.
     */
    public function emit(
        array $payload,
        string $apiKey,
        string $idempotencyKey,
        string $environment = 'development',
    ): DteResult {
        $this->requireCredentials($apiKey);

        if (trim($idempotencyKey) === '' || strlen($idempotencyKey) > 200) {
            throw new InvalidArgumentException('La clave de idempotencia debe ser estable y no vacía (máximo 200 caracteres).');
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $apiKey,
                'Idempotency-Key' => $idempotencyKey,
            ])
                ->acceptJson()
                ->asJson()
                ->withoutRedirecting()
                ->connectTimeout(5)
                ->timeout(30)
                ->post($this->endpoint($environment), $payload);
        } catch (ConnectionException) {
            return new DteResult(
                outcome: 'unknown',
                message: 'Resultado indeterminado por problema de conexión. Consultar antes de reintentar.',
            );
        }

        return $this->interpret($response, issuance: true);
    }

    public function getDocument(
        string $rut,
        int $type,
        int $folio,
        string $format,
        string $apiKey,
        string $environment = 'development',
    ): DteResult {
        $this->requireCredentials($apiKey);

        if (!preg_match('/\A[0-9]{7,8}-[0-9Kk]\z/D', $rut)) {
            throw new InvalidArgumentException('RUT del emisor inválido para consulta.');
        }

        if (!in_array($type, [33, 34, 39, 41, 52, 56, 61], true) || $folio < 1) {
            throw new InvalidArgumentException('Tipo DTE o folio inválido para consulta.');
        }

        if (!in_array($format, self::FORMATS, true)) {
            throw new InvalidArgumentException('Formato de consulta no permitido.');
        }

        $url = $this->endpoint($environment).'/'.$rut.'/'.$type.'/'.$folio.'/'.$format;

        try {
            $response = Http::withHeaders(['apikey' => $apiKey])
                ->acceptJson()
                ->withoutRedirecting()
                ->connectTimeout(5)
                ->timeout(30)
                ->get($url);
        } catch (ConnectionException) {
            return new DteResult(
                outcome: 'unknown',
                message: 'No fue posible consultar el proveedor DTE.',
            );
        }

        return $this->interpret($response, issuance: false);
    }

    private function endpoint(string $environment): string
    {
        return self::ENDPOINTS[$environment]
            ?? throw new InvalidArgumentException('Ambiente OpenFactura inválido.');
    }

    private function requireCredentials(string $apiKey): void
    {
        if (trim($apiKey) === '') {
            throw new InvalidArgumentException('Falta la API Key de la empresa emisora.');
        }
    }

    private function interpret(Response $response, bool $issuance): DteResult
    {
        $data = $response->json();

        if (!is_array($data)) {
            return new DteResult(
                outcome: 'unknown',
                httpStatus: $response->status(),
                message: 'Respuesta no interpretable. Verificar el estado antes de reintentar.',
            );
        }

        $error = $data['error'] ?? null;
        $providerCode = is_array($error) && is_string($error['code'] ?? null)
            ? $error['code']
            : null;
        $message = is_array($error) && is_string($error['message'] ?? null)
            ? $error['message']
            : (is_string($data['message'] ?? null) ? $data['message'] : null);
        $token = is_string($data['TOKEN'] ?? null) ? $data['TOKEN'] : null;
        $folio = is_int($data['FOLIO'] ?? null)
            ? $data['FOLIO']
            : (is_int($data['folio'] ?? null) ? $data['folio'] : null);
        $warning = is_string($data['WARNING'] ?? null) ? $data['WARNING'] : null;

        if ($response->successful() && $error === null) {
            if ($issuance && ($token === null || $token === '')) {
                return new DteResult(
                    outcome: 'unknown',
                    httpStatus: $response->status(),
                    folio: $folio,
                    warning: $warning,
                    message: 'Emisión sin TOKEN verificable. Se requiere conciliación.',
                    data: $data,
                );
            }

            return new DteResult(
                outcome: $issuance ? 'emitted' : 'retrieved',
                httpStatus: $response->status(),
                folio: $folio,
                token: $token,
                warning: $warning,
                data: $data,
            );
        }

        $uncertain = $providerCode === 'OF-06'
            || $response->serverError()
            || in_array($response->status(), [408, 429], true);

        return new DteResult(
            outcome: $uncertain ? 'unknown' : 'rejected',
            httpStatus: $response->status(),
            folio: $folio,
            token: $token,
            warning: $warning,
            providerCode: $providerCode,
            message: $message ?? 'El proveedor rechazó o no pudo procesar la solicitud.',
            data: $data,
        );
    }
}
