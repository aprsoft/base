<?php

use App\Services\Dte\V2\DteDocumentValidator;
use App\Services\Dte\V2\DteService;
use App\Services\Dte\V2\OpenFacturaClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

function makeDteV2(): array
{
    return [
        'Encabezado' => [
            'IdDoc' => ['TipoDTE' => 33, 'Folio' => 0, 'FchEmis' => '2026-10-30'],
            'Emisor' => ['RUTEmisor' => '11111111-1'],
            'Receptor' => ['RUTRecep' => '22222222-2'],
            'Totales' => ['MntTotal' => 1190],
        ],
        'Detalle' => [['NroLinDet' => 1, 'NmbItem' => 'Servicio', 'MontoItem' => 1190]],
    ];
}

function makeServiceV2(): DteService
{
    return new DteService(new DteDocumentValidator(), new OpenFacturaClient());
}

it('emits using JSON and a stable idempotency header without real HTTP', function () {
    Http::fake([
        'https://dev-api.haulmer.com/*' => Http::response([
            'TOKEN' => 'demo-token',
            'FOLIO' => 321,
            'XML' => 'demo-xml-base64',
        ], 200),
    ]);

    $result = makeServiceV2()->emit(makeDteV2(), 'demo-key', 'company:123:document:9');

    expect($result->emitted())->toBeTrue()
        ->and($result->folio)->toBe(321)
        ->and($result->token)->toBe('demo-token');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://dev-api.haulmer.com/v2/dte/document'
            && $request->hasHeader('apikey', 'demo-key')
            && $request->hasHeader('Idempotency-Key', 'company:123:document:9')
            && $request['response'] === ['FOLIO', 'XML']
            && $request['dte']['Encabezado']['IdDoc']['TipoDTE'] === 33;
    });

    Http::assertSentCount(1);
});

it('treats connection failures as uncertain instead of retrying automatically', function () {
    Http::fake([
        'https://dev-api.haulmer.com/*' => Http::failedConnection(),
    ]);

    $result = makeServiceV2()->emit(makeDteV2(), 'demo-key', 'company:123:document:9');

    expect($result->requiresReconciliation())->toBeTrue();
    Http::assertSentCount(1);
});

it('interprets definite validation rejections', function () {
    Http::fake([
        'https://dev-api.haulmer.com/*' => Http::response([
            'error' => ['code' => 'OF-01', 'message' => 'Campos obligatorios'],
        ], 400),
    ]);

    $result = makeServiceV2()->emit(makeDteV2(), 'demo-key', 'company:123:document:9');

    expect($result->outcome)->toBe('rejected')
        ->and($result->providerCode)->toBe('OF-01');
});

it('accepts a document lookup response without requiring an issuance token', function () {
    Http::fake([
        'https://dev-api.haulmer.com/*' => Http::response([
            'folio' => 321, 'json' => ['Encabezado' => [], 'Detalle' => []],
        ], 200),
    ]);

    $result = makeServiceV2()->get('11111111-1', 33, 321, 'json', 'demo-key');

    expect($result->outcome)->toBe('retrieved')
        ->and($result->folio)->toBe(321);
});
