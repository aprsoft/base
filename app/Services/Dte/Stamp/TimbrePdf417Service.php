<?php

declare(strict_types=1);

namespace App\Services\Dte\Stamp;

use DOMDocument;
use DOMXPath;
use InvalidArgumentException;

/**
 * Representa gráficamente el TED YA FIRMADO de un DTE (XML o Base64).
 * No crea firmas, no valida criptográficamente el CAF/FRMT, ni consulta al SII.
 */
final class TimbrePdf417Service
{
    private const DPI = 600;
    private const MODULO_X_PX = 5; // 5 / 600 pulgada = 8,33 mil >= 6,7 mil.
    private const MARGEN_PX = 150; // 0,25 pulgada en cada lado a 600 dpi.

    /**
     * @param string $entrada XML del DTE o XML codificado en Base64.
     * @return string Bytes binarios de la imagen PNG.
     */
    public function generar(string $entrada): string
    {
        $ted = $this->extraerTed($entrada);

        $codigo = new Pdf417BinarioSii(
            code: $ted,
            width: -self::MODULO_X_PX,
            height: -self::MODULO_X_PX,
            color: 'black',
            params: [4, 5],  // Relación de aspecto 4 y corrección ECL 5.
            padding: [
                self::MARGEN_PX,
                self::MARGEN_PX,
                self::MARGEN_PX,
                self::MARGEN_PX,
            ],
        );

        // Evita transparencias problemáticas al imprimir.
        $codigo->setBackgroundColor('white');

        return $codigo->getPngData();
    }

    /**
     * Extrae exactamente el texto del TED original, conservando bytes,
     * espacios, entidades, codificación y saltos de línea existentes.
     */
    public function extraerTed(string $entrada): string
    {
        $xml = $this->obtenerXml($entrada);

        // No permitimos DTDs ni entidades externas en documentos recibidos.
        if (preg_match('/<!DOCTYPE\b/i', $xml) === 1) {
            throw new InvalidArgumentException('El XML no debe contener DOCTYPE.');
        }

        $documento = new DOMDocument();
        $previo = libxml_use_internal_errors(true);

        try {
            if (!$documento->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT)) {
                throw new InvalidArgumentException('El contenido no es un XML válido.');
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previo);
        }

        $xpath = new DOMXPath($documento);
        $nodosTed = $xpath->query('//*[local-name()="TED"]');

        if ($nodosTed === false || $nodosTed->length !== 1) {
            throw new InvalidArgumentException('Se esperaba exactamente un TED en el XML.');
        }

        $ted = $nodosTed->item(0);
        foreach (['DD', 'FRMT'] as $elemento) {
            if ($xpath->query('./*[local-name()="' . $elemento . '"]', $ted)?->length !== 1) {
                throw new InvalidArgumentException("Falta el elemento {$elemento} del TED.");
            }
        }

        if ($xpath->query('./*[local-name()="DD"]/*[local-name()="CAF"]', $ted)?->length !== 1) {
            throw new InvalidArgumentException('El TED no incluye el CAF.');
        }

        // Extraer el fragmento del XML ORIGINAL, sin serializar con DOM:
        // la serialización podría cambiar la codificación o el contenido firmado.
        if (preg_match('~<(?P<tag>(?:[A-Za-z_][\w.-]*:)?TED)\b[^>]*>.*?</(?P=tag)\s*>~s', $xml, $partes) !== 1) {
            throw new InvalidArgumentException('No fue posible extraer el TED original.');
        }
        //dd($partes[0]);

        return $partes[0];
    }

    private function obtenerXml(string $entrada): string
    {
        $entrada = trim($entrada);

        if ($entrada === '') {
            throw new InvalidArgumentException('No se recibió un XML.');
        }

        if (str_starts_with(ltrim($entrada, "\xEF\xBB\xBF \t\n\r"), '<')) {
            return $entrada;
        }

        $xml = base64_decode($entrada, true);

        if ($xml === false || !str_starts_with(ltrim($xml, "\xEF\xBB\xBF \t\n\r"), '<')) {
            throw new InvalidArgumentException('La entrada no es XML ni Base64 de un XML.');
        }

        return $xml;
    }
}
