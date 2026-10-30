<?php

namespace App\Services\Dte\Post\Mapper;

use App\Models\Company;
use App\Models\Document;

class BoletaExenta
{
    public function __construct(
        private Document $document
    ) {}


    public function mapJsonDte(): string
    {
        if ((int) $this->document->typedocument->codigo !== 41) {
            throw new \LogicException(
                'Mapper solo soporta Boleta Exenta (TipoDTE 41)'
            );
        }

        $company  = Company::first();
        $customer = $this->document->customer;

        $dte = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE'     => $this->document->typedocument->codigo,
                    'Folio'       => 0,
                    'FchEmis'     => $this->document->fecha,
                    'IndServicio' => 3,
                ],
                'Emisor' => [
                    'RUTEmisor'    => $company->rut_emisor,
                    'RznSocEmisor' => $company->razon_social,
                    'GiroEmisor'   => $company->giro_emisor,
                    'DirOrigen'    => $company->direccion_origen,
                    'CmnaOrigen'   => $company->comuna_origen,
                ],
                'Receptor' => array_filter([
                    'RUTRecep'    => $customer->rutDte,
                    'RznSocRecep' => $customer->fullName,
                    'DirRecep'    => $customer->direccion_cliente,
                    'CmnaRecep'   => $customer->comuna,
                ], fn ($v) => $v !== null),
                'Totales' => [
                    'MntExe'   => (int) $this->document->neto,
                    'MntTotal' => (int) $this->document->neto,
                ],
            ],
            'Detalle' => $this->mapDetails(),
        ];

        $descuentos = $this->mapDiscount();
        if (!empty($descuentos)) {
            $dte['DscRcgGlobal'] = $descuentos;
        }

        $subtotales = $this->mapInformativeSubtotals();
        if (!empty($subtotales)) {
            $dte['SubTotInfo'] = $subtotales;
        }

        return json_encode([
            'response' => ['TIMBRE', 'FOLIO'],
            'dte'      => $dte,
        ], JSON_UNESCAPED_UNICODE);
    }

    protected function mapDetails(): array
    {
        return $this->document->details()
            ->vigente()
            ->servicios()
            ->get()
            ->map(fn ($detail, $index) => [
                'NroLinDet' => $index + 1,
                'NmbItem'   => $detail->detalle,
                'QtyItem'   => (int) $detail->cantidad,
                'PrcItem'   => (int) $detail->valor,
                'MontoItem' => (int) ($detail->cantidad * $detail->valor),
            ])
            ->values()
            ->toArray();
    }

    protected function mapDiscount(): array
    {
        return $this->document->details()
            ->vigente()
            ->descuento()
            ->get()
            ->map(fn ($detail) => [
                'TpoMov'   => 'D',
                'TpoValor' => '$',
                'ValorDR'  => abs((int) $detail->valor),
                'IndExeDR' => 1,
            ])
            ->values()
            ->toArray();
    }

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
}
