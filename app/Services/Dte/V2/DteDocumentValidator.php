<?php

declare(strict_types=1);

namespace App\Services\Dte\V2;

use InvalidArgumentException;

final class DteDocumentValidator
{
    private const SUPPORTED_TYPES = [33, 34, 39, 41, 52, 56, 61];

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
            throw new InvalidArgumentException('TipoDTE no soportado por esta integración.');
        }

        if (($id['Folio'] ?? null) !== 0) {
            throw new InvalidArgumentException('Para emitir por OpenFactura, IdDoc.Folio debe ser 0.');
        }

        $date = $id['FchEmis'] ?? null;
        if (!is_string($date) || !preg_match('/\A\d{4}-\d{2}-\d{2}\z/D', $date)) {
            throw new InvalidArgumentException('IdDoc.FchEmis debe tener formato YYYY-MM-DD.');
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));
        if (!checkdate($month, $day, $year)) {
            throw new InvalidArgumentException('IdDoc.FchEmis no es una fecha válida.');
        }

        foreach (['Emisor', 'Receptor', 'Totales'] as $section) {
            if (!isset($header[$section]) || !is_array($header[$section])) {
                throw new InvalidArgumentException("Falta Encabezado.$section.");
            }
        }

        foreach (['Emisor' => 'RUTEmisor', 'Receptor' => 'RUTRecep'] as $section => $field) {
            $value = $header[$section][$field] ?? null;
            if (!is_string($value) || trim($value) === '') {
                throw new InvalidArgumentException("Falta Encabezado.$section.$field.");
            }
        }

        $total = $header['Totales']['MntTotal'] ?? null;
        if (!is_int($total) && !is_float($total)) {
            throw new InvalidArgumentException('Totales.MntTotal debe ser numérico.');
        }

        if ($total < 0) {
            throw new InvalidArgumentException('Totales.MntTotal no puede ser negativo.');
        }

        $details = $dte['Detalle'] ?? null;
        if (!is_array($details) || !array_is_list($details) || $details === []) {
            throw new InvalidArgumentException('Detalle debe contener al menos una línea.');
        }

        foreach ($details as $index => $line) {
            if (!is_array($line) || !is_string($line['NmbItem'] ?? null)
                || trim($line['NmbItem']) === '') {
                throw new InvalidArgumentException("Detalle.$index.NmbItem es obligatorio.");
            }
        }

        if (in_array($type, [56, 61], true)) {
            $references = $dte['Referencia'] ?? null;
            if (!is_array($references) || !array_is_list($references) || $references === []) {
                throw new InvalidArgumentException('Notas de crédito y débito requieren Referencia.');
            }

            foreach ($references as $index => $reference) {
                if (!is_array($reference)
                    || !isset($reference['TpoDocRef'], $reference['FolioRef'], $reference['FchRef'], $reference['CodRef'])
                    || !in_array($reference['CodRef'], [1, 2, 3], true)) {
                    throw new InvalidArgumentException("Referencia.$index está incompleta o contiene CodRef inválido.");
                }
            }
        }
    }
}
