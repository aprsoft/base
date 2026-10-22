<?php

namespace App\Services\Dte\Post\Mapper;

use App\Models\Company;
use App\Models\Document;

class BoletaAfecta
{
    private Document $document;
    private Company  $company;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->company = Company::query()->firstOrFail();
    }

    public function mapJsonDte(): string
    {
        if ((int) $this->document->typedocument->codigo !== 39) {
            throw new \LogicException(
                'BoletaAfecta solo soporta Boleta Afecta (TipoDTE 39)'
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
                'Receptor' => [
                    'RUTRecep'    => $customer->rutDte,
                    'RznSocRecep' => $customer->fullName,
                    'DirRecep'    => $customer->direccion_cliente,
                    'CmnaRecep'   => $customer->comuna,
                ],
                'Totales' => [                   
                    'MntNeto'      => $this->document->neto,                                   
                    'IVA'          => $this->document->iva,                   
                    'MntTotal'     => $this->document->total,
                    
                ],
            ],
            'Detalle'      => $this->mapDetails(),
            'DscRcgGlobal' => $this->mapDiscount(),
            'SubTotInfo'   => $this->mapInformativeSubtotals(),
        ];

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
                'QtyItem'   => $detail->cantidad,
                'PrcItem'   => $detail->valor,
                'MontoItem' => $detail->cantidad * $detail->valor,
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
                'ValorDR'  => $detail->valor * -1,
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
