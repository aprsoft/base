<?php

declare(strict_types=1);

namespace App\Services\Dte\Post\Mapper;

use App\Models\Company;
use App\Models\Document;
use DomainException;
use LogicException;

class FacturaAfecta
{
    private Document $document;
    private Company $company;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->company = Company::query()->firstOrFail();
    }

    public function mapJsonDte(): string
    {
        if ((int) $this->document->typedocument?->codigo !== 33) {
            throw new LogicException('El mapper FacturaAfecta solo soporta TipoDTE 33.');
        }

        $customer = $this->document->customer;
        if ($customer === null) {
            throw new DomainException('El documento no tiene receptor.');
        }

        $idDoc = [
            'TipoDTE' => 33,
            'Folio'   => 0,
            'FchEmis' => $this->document->fecha,
        ];

        // Los campos de transacción existen en documents;
        // no se reemplazan por valores 1 arbitrarios.
        if ($this->document->TpoTranCompra !== null) {
            $idDoc['TpoTranCompra'] = (int) $this->document->TpoTranCompra;
        }
        if ($this->document->TpoTranVenta !== null) {
            $idDoc['TpoTranVenta'] = (int) $this->document->TpoTranVenta;
        }
        $payment = match ($this->document->fmaPago) {
            'CONTADO' => 1,
            'CREDITO' => 2,
            'SIN COSTO (ENTREGA GRATUITA)' => 3,
            null, 'NO INFORMADO' => null,
            default => throw new DomainException('Forma de pago no reconocida.'),
        };
        if ($payment !== null) {
            $idDoc['FmaPago'] = $payment;
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
                'Totales' => [
                    'MntNeto'      => $this->document->neto,
                    'TasaIVA'      => 19,
                    'IVA'          => $this->document->iva,
                    'MntTotal'     => $this->document->total,
                    'MontoPeriodo' => 2380,
                    'VlrPagar'     => 2380
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
