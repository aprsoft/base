<?php

namespace App\Services\Dte\Post\Mapper;

use App\Models\Company;
use App\Models\Document;

class GuiaDespacho
{
    
    private Document $document;
    private Company $company;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->company = Company::query()->firstOrFail();
    }
    
    public function map(): string
    {
        if ((int) $this->document->typedocument->codigo !== 52) {
            throw new \LogicException(
                'GuíaDespacho solo soporta TipoDTE 52'
            );
        }

        $this->company = Company::first();

        $dte = [
            'Encabezado' => [
                'IdDoc' => array_filter([
                    'TipoDTE'       => 52,
                    'Folio'         => $this->document->folio,
                    'FchEmis'       => $this->document->fecha,
                    'TipoDespacho'  => $this->document->tipo_despacho ?? null, // opcional
                    'IndTraslado'   => $this->document->ind_traslado,          // obligatorio
                    'TpoImpresion'  => $this->document->tipo_impresion ?? null,
                    'CdgSIISucur'   => $this->document->codigo_sucursal ?? null,
                ]),

                'Emisor' => array_filter([
                    'RUTEmisor'    => $this->company->rut_emisor,
                    'RznSoc'       => $this->company->razon_social,
                    'GiroEmis'     => $this->company->giro_emisor,
                    'Acteco'       => $this->company->acteco,
                    'DirOrigen'    => $this->company->direccion_origen,
                    'CmnaOrigen'   => $this->company->comuna_origen,
                    'CiudadOrigen' => $this->company->ciudad_origen ?? null,
                    'Telefono'     => $this->company->telefono ?? null,
                    'CorreoEmisor' => $this->company->email ?? null,
                ]),

                'Receptor' => array_filter([
                    'RUTRecep'     => $this->document->customer->rutDte,
                    'RznSocRecep'  => $this->document->customer->fullName,
                    'GiroRecep'    => $this->document->customer->giro,
                    'DirRecep'     => $this->document->customer->direccion_cliente,
                    'CmnaRecep'    => $this->document->customer->comuna,
                    'CiudadRecep'  => $this->document->customer->ciudad ?? null,
                    'Contacto'     => $this->document->customer->contacto ?? null,
                    'CorreoRecep'  => $this->document->customer->email ?? null,
                ]),

                // Totales en Guía son informativos (no tributarios)
                'Totales' => array_filter([
                    'MntExe'   => $this->document->neto ?? null,
                    'MntTotal' => $this->document->neto ?? null,
                ]),
            ],

            'Detalle'      => $this->mapDetails(),
            'DscRcgGlobal' => $this->mapDiscount(),
            'SubTotInfo'   => $this->mapInformativeSubtotals(),
            'Referencia'   => $this->mapReferences(),
            'Transporte'   => $this->mapTransport(),
            'Aduana'       => $this->mapAduana(),
        ];

        return json_encode([
            'response' => ['TIMBRE', 'FOLIO'],
            'dte'      => array_filter($dte),
        ], JSON_UNESCAPED_UNICODE);
    }

    /* ===================== DETALLE ===================== */

    protected function mapDetails(): array
    {
        return $this->document->details()
            ->vigente()
            ->get()
            ->map(fn ($detail, $index) => array_filter([
                'NroLinDet' => $index + 1,
                'NmbItem'   => $detail->detalle,
                'QtyItem'   => $detail->cantidad,
                'PrcItem'   => $detail->valor,
                'MontoItem' => $detail->cantidad * $detail->valor,
                'IndExe'    => 1, // Guía: ítems exentos/informativos
            ]))
            ->values()
            ->toArray();
    }

    /* ===================== DESCUENTOS ===================== */

    protected function mapDiscount(): array
    {
        return $this->document->details()
            ->vigente()
            ->descuento()
            ->get()
            ->map(fn ($detail) => [
                'TpoMov'   => 'D',
                'TpoValor' => '$',
                'ValorDR'  => abs($detail->valor),
                'IndExeDR' => 1,
            ])
            ->values()
            ->toArray();
    }

    /* ===================== SUBTOTALES INFO ===================== */

    protected function mapInformativeSubtotals(): array
    {
        return $this->document->details()
            ->vigente()
            ->informativo()
            ->get()
            ->map(fn ($detail, $index) => [
                'NroSTI'       => $index + 1,
                'GlosaSTI'     => $detail->detalle,
                'SubTotExeSTI' => abs((int) $detail->valor),
            ])
            ->values()
            ->toArray();
    }

    /* ===================== REFERENCIAS ===================== */

    protected function mapReferences(): array
    {
        return $this->document->references()
            ->get()
            ->map(fn ($ref, $index) => array_filter([
                'NroLinRef' => $index + 1,
                'TpoDocRef' => $ref->tipo_documento,
                'FolioRef'  => $ref->folio,
                'FchRef'    => $ref->fecha,
                'RazonRef'  => $ref->razon,
            ]))
            ->values()
            ->toArray();
    }

    /* ===================== TRANSPORTE ===================== */

    protected function mapTransport(): ?array
    {
        if (!$this->document->transporte) {
            return null;
        }

        return array_filter([
            'RutTrans'   => $this->document->transporte->rut,
            'NombreTrans'=> $this->document->transporte->nombre,
            'Patente'   => $this->document->transporte->patente,
            'RUTChofer' => $this->document->transporte->rut_chofer,
            'NombreChofer' => $this->document->transporte->nombre_chofer,
            'DirDest'   => $this->document->direccion_destino,
            'CmnaDest'  => $this->document->comuna_destino,
            'CiudadDest'=> $this->document->ciudad_destino,
        ]);
    }

    /* ===================== ADUANA (EXPORTACIÓN) ===================== */

    protected function mapAduana(): ?array
    {
        if (!$this->document->aduana) {
            return null;
        }

        return array_filter([
            'CodModVenta' => $this->document->aduana->modo_venta,
            'CodClauVenta'=> $this->document->aduana->clausula_venta,
            'TotClauVenta'=> $this->document->aduana->total_clausula,
            'CodViaTransp'=> $this->document->aduana->via_transporte,
            'CodPtoEmbarq'=> $this->document->aduana->puerto_embarque,
            'IdAdicPtoEmb'=> $this->document->aduana->puerto_destino,
        ]);
    }
}
