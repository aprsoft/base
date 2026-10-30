<?php

use App\Services\Dte\V2\DteDocumentValidator;

function validDteV2(int $type = 33): array
{
    $dte = [
        'Encabezado' => [
            'IdDoc' => ['TipoDTE' => $type, 'Folio' => 0, 'FchEmis' => '2026-10-30'],
            'Emisor' => ['RUTEmisor' => '11111111-1'],
            'Receptor' => ['RUTRecep' => '22222222-2'],
            'Totales' => ['MntTotal' => 1190],
        ],
        'Detalle' => [['NroLinDet' => 1, 'NmbItem' => 'Servicio de prueba', 'MontoItem' => 1190]],
    ];

    if (in_array($type, [56, 61], true)) {
        $dte['Referencia'] = [
            ['TpoDocRef' => 33, 'FolioRef' => 100, 'FchRef' => '2026-10-29', 'CodRef' => 3],
        ];
    }

    return $dte;
}

it('validates seven supported DTE types', function () {
    $validator = new DteDocumentValidator();

    foreach ([33, 34, 39, 41, 52, 56, 61] as $type) {
        $validator->validate(validDteV2($type));
    }

    expect(true)->toBeTrue();
});

it('rejects missing references in credit notes', function () {
    $dte = validDteV2(61);
    unset($dte['Referencia']);

    expect(fn () => (new DteDocumentValidator())->validate($dte))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects nonzero folios before issuance', function () {
    $dte = validDteV2();
    $dte['Encabezado']['IdDoc']['Folio'] = 99;

    expect(fn () => (new DteDocumentValidator())->validate($dte))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects invalid emission dates', function () {
    $dte = validDteV2();
    $dte['Encabezado']['IdDoc']['FchEmis'] = '2026-02-30';

    expect(fn () => (new DteDocumentValidator())->validate($dte))
        ->toThrow(InvalidArgumentException::class);
});
