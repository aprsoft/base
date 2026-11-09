<?php

use App\Services\Dte\TimbrePdf417Service;

it('conserva el TED original tanto desde XML como desde Base64', function () {
    $ted = '<TED version="1.0"><DD><RE>11111111-1</RE><CAF version="1.0"/></DD><FRMT algoritmo="SHA1withRSA">firma</FRMT></TED>';
    $xml = '<?xml version="1.0" encoding="ISO-8859-1"?><DTE>' . $ted . '</DTE>';

    $servicio = new TimbrePdf417Service();

    expect($servicio->extraerTed($xml))->toBe($ted);
    expect($servicio->extraerTed(base64_encode($xml)))->toBe($ted);
});

it('rechaza un XML que no contenga un TED', function () {
    (new TimbrePdf417Service())->extraerTed('<DTE/>');
})->throws(InvalidArgumentException::class);

it('usa compactación binaria y nivel de corrección 5', function () {
    $codigo = new \App\Services\Dte\Pdf417BinarioSii(
        code: str_repeat('A', 24),
        width: -5,
        height: -5,
        color: 'black',
        params: [4, 5],
        padding: [150, 150, 150, 150],
    );

    $filas = $columnas = $nivel = 0;
    $palabras = $codigo->getCodewords($filas, $columnas, $nivel);

    expect($nivel)->toBe(5);
    // Palabra 0 = descriptor de longitud; palabra 1 = inicio de Byte Compaction.
    expect($palabras[1])->toBe(924);
});
