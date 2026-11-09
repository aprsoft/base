<?php

declare(strict_types=1);

namespace App\Services\Dte\Stamp;

use Com\Tecnick\Barcode\Type\Square\PdfFourOneSeven;

/**
 * PDF417 no truncado y con codificación exclusivamente binaria.
 *
 * La librería original selecciona los modos de compactación automáticamente;
 * el SII exige Byte Compaction Mode para su timbre electrónico.
 */
final class Pdf417BinarioSii extends PdfFourOneSeven
{
    // Una fila PDF417 debe medir 3 veces el ancho del módulo X.
    protected int $row_height = 3;

    // La zona blanca exterior se agrega, en píxeles, mediante padding.
    protected int $quiet_horizontal = 0;
    protected int $quiet_vertical = 0;

    /** @return array<int, array{int, string}> */
    protected function getInputSequences(string $code): array
    {
        // 924 para cantidades múltiplos de seis bytes, 901 en otros casos.
        // Ambos modos son Byte Compaction Mode, nunca texto ni numérico.
        return [[strlen($code) % 6 === 0 ? 924 : 901, $code]];
    }
}
