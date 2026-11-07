<?php

declare(strict_types=1);

namespace App\Services\Dte\Post\Mapper;

use App\Models\Company;
use App\Models\Document;
use DomainException;
use LogicException;

class BoletaAfecta
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
        if ((int) $this->document->typedocument?->codigo !== 39) {
            throw new LogicException('El mapper BoletaAfecta solo soporta TipoDTE 39.');
        }

        $customer = $this->document->customer;
        if ($customer === null) {
            throw new DomainException('El documento no tiene receptor.');
        }

        $dte = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE'     => 39,
                    'Folio'       => 0,
                    'FchEmis'     => $this->document->fecha,
                    'IndServicio' => 3,
                ],
                'Emisor' => [
                    'RUTEmisor'    => $this->company->rut_emisor,
                    'RznSocEmisor' => $this->company->razon_social,
                    'GiroEmisor'   => $this->company->giro_emisor,
                    'DirOrigen'    => $this->company->direccion_origen,
                    'CmnaOrigen'   => $this->company->comuna_origen,
                ],
                'Receptor' => [
                    'RUTRecep'    => $customer->rutDte,
                    'RznSocRecep' => $customer->fullName,
                    'DirRecep'    => $customer->direccion_cliente,
                    'CmnaRecep'   => $customer->comuna,
                ],
                'Totales' => [
                    'MntExe'       => $this->document->exento,
                    'MntNeto'      => $this->document->neto,
                    'IVA'          => $this->document->iva,
                    'MntTotal'     => $this->document->total,
                    'TotalPeriodo' => $this->document->total,
                    'SaldoAnterior'=> 0,
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
