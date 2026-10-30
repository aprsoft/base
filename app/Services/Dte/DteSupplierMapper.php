<?php

namespace App\Services\Dte;

class DteSupplierMapper
{
    public static function map(array $data): array
    {
        $encabezado = $data['Encabezado'];
        $idDoc      = $encabezado['IdDoc'];
        $totales    = $encabezado['Totales'];
        $emisor     = $encabezado['Emisor'];

        return [
            'emisor' => [
                'rut'       => $emisor['RUTEmisor'],
                'razon'     => $emisor['RznSoc'],
                'giro'      => $emisor['GiroEmis'] ?? 'POR DEFINIR',
                'direccion' => $emisor['DirOrigen'] ?? 'POR DEFINIR',
                'telefono'  => $emisor['Telefono'] ?? null,
                'comuna'    => $emisor['CmnaOrigen'] ?? null,
                'ciudad'    => $emisor['CiudadOrigen'] ?? null,
                'email'     => $emisor['CorreoEmisor'] ?? null,
                'acteco'    => $emisor['Acteco'] ?? [],
            ],

            'documento' => [
                'tipo_dte' => $idDoc['TipoDTE'],
                'folio'    => $idDoc['Folio'],
                'fecha'    => $idDoc['FchEmis'] ?? null,
                'fma_pago' => $idDoc['FmaPago'] ?? 4,
            ],

            'totales' => [
                'exento' => $totales['MntExe'] ?? 0,
                'neto'   => $totales['MntNeto'] ?? 0,
                'iva'    => $totales['IVA'] ?? 0,
                'tasa'   => $totales['TasaIVA'] ?? 19,
                'total'  => $totales['MntTotal'] ?? 0,
            ],

            'detalle' => $data['Detalle'] ?? [],

            'referencia' => $data['Referencia'] ?? null,
        ];
    }
}
