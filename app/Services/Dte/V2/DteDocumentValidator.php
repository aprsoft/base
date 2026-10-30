<?php

declare(strict_types=1);

namespace App\Services\Dte\V2;

use InvalidArgumentException;

/**
 * Structural preflight only. SII/OpenFactura-specific fiscal validation
 * remains authoritative. Does NOT derive amounts or invent fiscal values.
 */
final class DteDocumentValidator
{
    public const SUPPORTED_TYPES = [33, 34, 39, 41, 52, 56, 61];

    public function validate(array $dte): void
    {
        $header = $dte['Encabezado'] ?? null;
        if (!is_array($header)) {
            throw new InvalidArgumentException('Falta Encabezado.');
        }
        $id = $header['IdDoc'] ?? null;
        if (!is_array($id)) {
            throw new InvalidArgumentException('Falta Encabezado.IdDoc.');
        }
        $type = $id['TipoDTE'] ?? null;
        if (!is_int($type) || !in_array($type, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException('TipoDTE no soportado en esta integración.');
        }
        if (($id['Folio'] ?? null) !== 0) {
            throw new InvalidArgumentException('Folio debe ser 0 para una emisión.');
        }
        $date = $id['FchEmis'] ?? null;
        if (!is_string($date) || !preg_match('/\A\d{4}-\d{2}-\d{2}\z/D', $date)) {
            throw new InvalidArgumentException('FchEmis debe tener formato YYYY-MM-DD.');
        }
        [$year, $month, $day] = array_map('intval', explode('-', $date));
        if (!checkdate($month, $day, $year)) {
            throw new InvalidArgumentException('Fecha de emisión inválida.');
        }

        foreach (['Emisor', 'Receptor', 'Totales'] as $section) {
            if (!is_array($header[$section] ?? null)) {
                throw new InvalidArgumentException("Falta Encabezado.$section.");
            }
        }
        foreach (['Emisor' => 'RUTEmisor', 'Receptor' => 'RUTRecep'] as $section => $field) {
            if (!is_string($header[$section][$field] ?? null)
                || trim($header[$section][$field]) === '') {
                throw new InvalidArgumentException("Falta $section.$field.");
            }
        }
        $total = $header['Totales']['MntTotal'] ?? null;
        if ((!is_int($total) && !is_float($total)) || !is_finite((float) $total) || $total < 0) {
            throw new InvalidArgumentException('MntTotal debe ser un número no negativo.');
        }

        $details = $dte['Detalle'] ?? null;
        if (!is_array($details) || !array_is_list($details) || $details === []) {
            throw new InvalidArgumentException('Detalle requiere una lista no vacía.');
        }
        foreach ($details as $i => $line) {
            if (!is_array($line)
                || !is_string($line['NmbItem'] ?? null)
                || trim($line['NmbItem']) === '') {
                throw new InvalidArgumentException("Detalle[$i].NmbItem es obligatorio.");
            }
        }

        if (in_array($type, [56, 61], true)) {
            $references = $dte['Referencia'] ?? null;
            if (!is_array($references) || !array_is_list($references) || $references === []) {
                throw new InvalidArgumentException('Nota de crédito/débito requiere Referencia.');
            }
            foreach ($references as $i => $ref) {
                if (!is_array($ref)
                    || !isset($ref['TpoDocRef'], $ref['FolioRef'], $ref['FchRef'], $ref['CodRef'])
                    || !in_array($ref['CodRef'], [1, 2, 3], true)) {
                    throw new InvalidArgumentException("Referencia[$i] inválida.");
                }
            }
        }

        if ($type === 52 && !isset($id['IndTraslado'])) {
            throw new InvalidArgumentException('Guía de despacho requiere IdDoc.IndTraslado.');
        }

        if (isset($dte['DscRcgGlobal'])) {
            if (!is_array($dte['DscRcgGlobal']) || !array_is_list($dte['DscRcgGlobal'])) {
                throw new InvalidArgumentException('DscRcgGlobal debe ser una lista.');
            }
            foreach ($dte['DscRcgGlobal'] as $i => $discount) {
                if (!is_array($discount)
                    || !isset($discount['NroLinDR'], $discount['TpoMov'], $discount['TpoValor'], $discount['ValorDR'])) {
                    throw new InvalidArgumentException("DscRcgGlobal[$i] incompleto.");
                }
            }
        }
    }
}
