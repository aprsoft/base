<?php

declare(strict_types=1);

namespace App\Services\Dte\Post\Mapper;

use App\Models\Company;
use App\Models\Document;
use DomainException;
use LogicException;

class NotaCredito
{
    private Document $document;
    private Company $company;
    private int $CodRef;

    public function __construct(Document $document, int $CodRef)
    {
        $this->document = $document;
        $this->company = Company::query()->firstOrFail();
        $this->CodRef;
    }

    public function mapJsonDte(): string
    {
        if ((int) $this->document->typedocument?->codigo !== 61) {
            throw new LogicException('El mapper NotaCredito solo soporta TipoDTE 61.');
        }

        $customer = $this->document->customer;
       
        $parent = $this->document->parent;
        if ($customer === null || $parent?->typedocument === null || $parent->folio === null) {
            throw new DomainException('La nota requiere receptor y documento de referencia con folio.');
        }
        // CodRef existe físicamente en documents. No asumir que toda nota anula.
        if (! in_array((int) $this->document->CodRef, [1, 2, 3], true)) {
            throw new DomainException('La nota no tiene un CodRef válido.');
        }
        // El mapper original no soporta descuentos globales en notas;
        // impedir pérdida silenciosa de ese dato.
        if ($this->document->details()->vigente()->descuento()->exists()) {
            throw new DomainException('No se admite emitir una nota con descuentos globales sin definir su mapeo.');
        }

        $referencia = [[
            'NroLinRef' => 1,
            'TpoDocRef' => (int) $parent->typedocument->codigo,
            'FolioRef'  => (int) $parent->folio,
            'FchRef'    => $parent->fecha,
            'CodRef'    => $CodRef,
        ]];

        $dte = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 61,
                    'Folio'   => 0,
                    'FchEmis' => $this->document->fecha,
                    'MntBruto' => 1,
                ],
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
                'Totales' => [
                    'MntNeto'  => $this->document->neto,
                    'TasaIVA'  => 19,
                    'IVA'      => $this->document->iva,
                    'MntTotal' => $this->document->total,
                ],
            ],
            'Detalle'    => $this->mapDetails(),
            'Referencia' => $referencia,
        ];

        $subtotals = $this->mapInformativeSubtotals();
        if ($subtotals !== []) {
            $dte['SubTotInfo'] = $subtotals;
        }

        return json_encode(
            ['response' => ['TIMBRE', 'FOLIO','PDF'], 'dte' => $dte],
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
                'NroLinDet'     => $index + 1,
                'NmbItem'       => $detail->detalle,
                'QtyItem'       => (int) $detail->QtyItem,
                'PrcItem'       => (int) $detail->PrcItem,
                'MontoItem'     => (int) $detail->MontoItem,
            ];
            if ($detail->IndExe !== null && (int) $detail->IndExe > 0) {
                $line['IndExe'] = (int) $detail->IndExe;
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
