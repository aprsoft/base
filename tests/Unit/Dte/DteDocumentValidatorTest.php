<?php

use App\Services\Dte\V2\DteDocumentValidator;
use App\Services\Dte\V2\DteFactory;

function validDteV2(int $type = 33): array
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
            ['TpoDocRef' => 33, 'FolioRef' => 100, 'FchRef' => '2026-10-29', 'CodRef' => 3],
        ];
    }
    return $dte;
}

it('accepts valid minimal shapes for seven types', function () {
    foreach (DteDocumentValidator::SUPPORTED_TYPES as $type) {
        $dte = validDteV2($type);
        (new DteDocumentValidator())->validate($dte);
        expect((new DteFactory())->resolve($type)->map($dte))->toBe($dte);
    }
});

it('rejects invalid dates, folios, missing notes references and incomplete discounts', function () {
    $invalid = [];
    $x = validDteV2(); $x['Encabezado']['IdDoc']['FchEmis'] = '2026-02-30'; $invalid[] = $x;
    $x = validDteV2(); $x['Encabezado']['IdDoc']['Folio'] = 50; $invalid[] = $x;
    $x = validDteV2(61); unset($x['Referencia']); $invalid[] = $x;
    $x = validDteV2(); $x['DscRcgGlobal'] = [['ValorDR' => 20]]; $invalid[] = $x;
    foreach ($invalid as $dte) {
        try {
            (new DteDocumentValidator())->validate($dte);
            test()->fail('Expected invalid DTE rejection');
        } catch (InvalidArgumentException) {
            expect(true)->toBeTrue();
        }
    }
});
