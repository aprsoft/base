<?php

declare(strict_types=1);

namespace App\Services\Dte\Post\Mapper;

use App\Models\Company;
use App\Models\Document;
use DomainException;
use LogicException;

class GuiaDespacho
{
    private Document $document;
    private Company $company;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->company = Company::query()->firstOrFail();
    }

    /**
     * IndTraslado es obligatorio para la guía (52), pero NO tiene
     * columna en la migración documents. Se recibe explícitamente.
     * El DteSendingManager actual no le pasa este dato y, por
     * seguridad, no puede emitir una guía hasta resolver ese origen.
     */
    public function mapJsonDte(?int $indTraslado = null): string
    {
        if ((int) $this->document->typedocument?->codigo !== 52) {
            throw new LogicException('El mapper GuiaDespacho solo soporta TipoDTE 52.');
        }
        if ($indTraslado === null || ! in_array($indTraslado, range(1, 9), true)) {
            throw new DomainException(
                'La guía requiere IndTraslado validado: no existe en la migración documents.'
            );
        }

        $customer = $this->document->customer;
        if ($customer === null) {
            throw new DomainException('La guía no tiene receptor.');
        }

        $idDoc = [
            'TipoDTE'    => 52,
            'Folio'      => 0,
            'FchEmis'    => $this->document->fecha,
            'IndTraslado'=> $indTraslado,
        ];
        if ($this->document->CdgSIISucur !== null) {
            $idDoc['CdgSIISucur'] = (int) $this->document->CdgSIISucur;
        }

        $dte = [
            'Encabezado' => [
                'IdDoc' => $idDoc,
                'Emisor' => [
                    'RUTEmisor' => $this->company->rut_emisor,
                    'RznSoc'    => $this->company->razon_social,
                    'GiroEmis'  => $this->company->giro_emisor,
                    'Acteco'    => $this->company->acteco,
                    'DirOrigen' => $this->company->direccion_origen,
                    'CmnaOrigen'=> $this->company->comuna_origen,
                ],
                'Receptor' => [
                    'RUTRecep'    => $customer->rutDte,
                    'RznSocRecep' => $customer->fullName,
                    'GiroRecep'   => $customer->giro,
                    'DirRecep'    => $customer->direccion_cliente,
                    'CmnaRecep'   => $customer->comuna,
                ],
                // El total usa el accessor existente de Document.
                // Su regla tributaria debe verificarse para cada traslado.
                'Totales' => [
                    'MntTotal' => $this->document->total,
                ],
            ],
            'Detalle' => $this->mapDetails(),
        ];

        $discounts = $this->mapDiscount();
        if ($discounts !== []) {
            $dte['DscRcgGlobal'] = $discounts;
        }
        $subtotals = $this->mapInformativeSubtotals();
        if ($subtotals !== []) {
            $dte['SubTotInfo'] = $subtotals;
        }

        $parent = $this->document->parent;
        if ($parent !== null) {
            if ($parent->typedocument?->codigo === null || $parent->folio === null) {
                throw new DomainException('La referencia de la guía no tiene tipo o folio.');
            }
            $dte['Referencia'] = [[
                'NroLinRef' => 1,
                'TpoDocRef' => (int) $parent->typedocument->codigo,
                'FolioRef'  => (int) $parent->folio,
                'FchRef'    => $parent->fecha,
            ]];
        }

        // No se inventan relaciones transporte/aduana ni sus columnas.
        return json_encode(
            ['response' => ['TIMBRE', 'FOLIO'], 'dte' => $dte],
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }


    protected function mapDetails(): array
    {
        $details = $this->document->details()
            ->vigente()
            ->servicios()
            // El service_id 10 es descuento; no debe duplicarse como ítem.
            ->where('service_id', '!=', 10)
            ->get();

        if ($details->isEmpty()) {
            throw new DomainException('El documento no tiene ítems vigentes para el DTE.');
        }

        return $details->values()->map(function ($detail, int $index): array {
            if ($detail->QtyItem === null || $detail->PrcItem === null || $detail->MontoItem === null) {
                throw new DomainException('Faltan cantidad, precio o monto en un detalle.');
            }

            $line = [
                'NroLinDet' => $index + 1,
                'NmbItem'   => $detail->detalle,
                'QtyItem'   => (int) $detail->QtyItem,
                'PrcItem'   => (int) $detail->PrcItem,
                'MontoItem' => (int) $detail->MontoItem,
            ];
            if ($detail->IndExe !== null && (int) $detail->IndExe > 0) {
                $line['IndExe'] = (int) $detail->IndExe;
            }

            return $line;
        })->all();
    }

    protected function mapDiscount(): array
    {
        return $this->document->details()->vigente()->descuento()->get()
            ->values()->map(function ($detail, int $index): array {
                // MontoItem es una columna física. Aquí representa el valor
                // del registro de descuento (service_id 10); confirmar
                // su convención con los datos reales antes de emitir.
                if ($detail->MontoItem === null) {
                    throw new DomainException('Falta MontoItem en un descuento.');
                }

                $line = [
                    'NroLinDR' => $index + 1,
                    'TpoMov'   => 'D',
                    'TpoValor' => '$',
                    'ValorDR'  => abs((int) $detail->MontoItem),
                ];
                if ($detail->IndExe !== null && in_array((int) $detail->IndExe, [1, 2], true)) {
                    $line['IndExeDR'] = (int) $detail->IndExe;
                }

                return $line;
            })->all();
    }

    protected function mapInformativeSubtotals(): array
    {
        return $this->document->details()->vigente()->informativo()->get()
            ->values()->map(function ($detail, int $index): array {
                if ($detail->MontoItem === null) {
                    throw new DomainException('Falta MontoItem en un subtotal informativo.');
                }

                return [
                    'NroSTI'       => $index + 1,
                    'GlosaSTI'     => $detail->detalle,
                    'SubTotExeSTI' => abs((int) $detail->MontoItem),
                ];
            })->all();
    }

}
