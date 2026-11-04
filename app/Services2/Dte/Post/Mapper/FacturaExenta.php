<?php

namespace App\Services\Dte\Post\Mapper;

use App\Models\Company;
use App\Models\Document;


class FacturaExenta
{  
    private Document $document;
    private Company  $company;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->company = Company::query()->firstOrFail();
    }

    public function map(Document $document): string
    {
     
        if ((int) $document->typedocument->codigo !== 34) {
            throw new \LogicException(
                'Factura Exenta solo soporta Factura Exenta (TipoDTE 34)'
            );
        }

       $this->company  = Company::first();

        // FmaPago 
        // 1   Contado                      
        // 2   Crédito                       
        // 3   Sin costo (entrega gratuita)  


        $dte = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' =>  $document->typedocument->codigo,
                    'Folio'   => 0,
                    'FchEmis' => $document->fecha,
                    'TpoTranCompra'=>1,
                    'TpoTranVenta'=>1,
                    'FmaPago'=>2,
                ],

                'Emisor' => [
                    'RUTEmisor'    =>$this->company->rut_emisor,
                    'RznSoc'       =>$this->company->razon_social,
                    'GiroEmis'     =>$this->company->giro_emisor,
                    'Acteco'       =>$this->company->acteco,
                    'DirOrigen'    =>$this->company->direccion_origen,
                    'CmnaOrigen'   =>$this->company->comuna_origen,
                ],
                'Receptor' => [
                    'RUTRecep'    => $document->customer->rutDte,
                    'RznSocRecep' => $document->customer->fullName,                    
                    'GiroRecep'   => $document->customer->giro,
                    'DirRecep'    => $document->customer->direccion_cliente,
                    'CmnaRecep'   => $document->customer->comuna,
                ],

                
                'Totales' => [
                    'MntExe'   => $this->document->neto,
                    'MntTotal' => $this->document->neto,                  
                ],
            ],

            // 🔹 Ítems afectos (IndExe = 0 o ausente)
            'Detalle'      => $this->mapDetails($document),          
            'DscRcgGlobal' => $this->mapDiscount($document),
            'SubTotInfo'   => $this->mapInformativeSubtotals($document),
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
