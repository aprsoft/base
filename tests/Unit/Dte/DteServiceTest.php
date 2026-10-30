<?php

use App\Services\Dte\V2\DteDocumentValidator;
use App\Services\Dte\V2\DteFactory;
use App\Services\Dte\V2\DteService;
use App\Services\Dte\V2\OpenFacturaClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

function testDte(int $type = 33): array
{
    $dte = [
        'Encabezado' => [
            'IdDoc' => ['TipoDTE' => $type, 'Folio' => 0, 'FchEmis' => '2026-10-30'],
            'Emisor' => ['RUTEmisor' => '11111111-1'],
            'Receptor' => ['RUTRecep' => '22222222-2'],
            'Totales' => ['MntTotal' => 1190],
        ],
        'Detalle' => [['NroLinDet' => 1, 'NmbItem' => 'Servicio', 'MontoItem' => 1190]],
    ];
    if ($type === 52) {
        $dte['Encabezado']['IdDoc']['IndTraslado'] = 1;
    }
    if (in_array($type, [56, 61], true)) {
        $dte['Referencia'] = [
            ['NroLinRef' => 1, 'TpoDocRef' => 33, 'FolioRef' => 100, 'FchRef' => '2026-10-29', 'CodRef' => 3],
        ];
    }
    return $dte;
}

function newDteService(): DteService
{
    return new DteService(new DteDocumentValidator(), new DteFactory(), new OpenFacturaClient());
}

it('emits all seven types without real HTTP and preserves fiscal input', function () {
    Http::preventStrayRequests();
    Http::fake(['https://dev-api.haulmer.com/*' => Http::response(['TOKEN' => 'demo', 'FOLIO' => 123], 200)]);
    foreach ([33, 34, 39, 41, 52, 56, 61] as $type) {
        $result = newDteService()->emit(testDte($type), 'test-key', 'emitter:type'.$type.':doc123');
        expect($result->emitted())->toBeTrue()
            ->and($result->folio)->toBe(123)
            ->and($result->token)->toBe('demo');
    }
    Http::assertSentCount(7);
    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://dev-api.haulmer.com/v2/dte/document'
            && $request->hasHeader('apikey', 'test-key')
            && $request->hasHeader('Idempotency-Key')
            && $request['response'] === ['FOLIO', 'XML'];
    });
});

it('never retries when connection result is unknown', function () {
    Http::preventStrayRequests();
    Http::fake(['https://dev-api.haulmer.com/*' => Http::failedConnection()]);
    $result = newDteService()->emit(testDte(), 'key', 'issuer:operation:1');
    expect($result->requiresReconciliation())->toBeTrue();
    Http::assertSentCount(1);
});

it('preserves OF-06 for reconciliation', function () {
    Http::fake(['https://dev-api.haulmer.com/*' => Http::response([
        'error' => ['code' => 'OF-06', 'message' => 'Duplicado'],
        'TOKEN' => 'earlier-token',
    ], 409)]);
    $result = newDteService()->emit(testDte(), 'key', 'issuer:operation:1');
    expect($result->outcome)->toBe('unknown')
        ->and($result->providerCode)->toBe('OF-06')
        ->and($result->token)->toBe('earlier-token');
});

it('supports issued and received list endpoints and status lookups', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://dev-api.haulmer.com/v2/dte/document/issued' => Http::response([
            'current_page' => 1, 'last_page' => 1, 'total' => 0, 'data' => [],
        ], 200),
        'https://dev-api.haulmer.com/v2/dte/document/received' => Http::response([
            'current_page' => 1, 'last_page' => 1, 'total' => 0, 'data' => [],
        ], 200),
        'https://dev-api.haulmer.com/v2/dte/document/11111111-1/33/123/status' => Http::response([
            'status' => 'Aceptado', 'folio' => '123',
        ], 200),
    ]);
    $service = newDteService();
    expect($service->issued(['Page' => '1'], 'key')->outcome)->toBe('retrieved')
        ->and($service->received(['Page' => '1'], 'key')->outcome)->toBe('retrieved')
        ->and($service->get('11111111-1', 33, 123, 'status', 'key')->data['status'])->toBe('Aceptado');
    Http::assertSentCount(3);
});

it('rejects notes without references before sending any request', function () {
    Http::fake();
    $dte = testDte(56);
    unset($dte['Referencia']);
    expect(fn () => newDteService()->emit($dte, 'key', 'issuer:note:1'))
        ->toThrow(InvalidArgumentException::class);
    Http::assertNothingSent();
});
