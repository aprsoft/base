<?php

namespace App\Services\Dte\Post\Mapper;

use App\Models\Company;
use App\Models\Document;


class NotaDebito
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
     
        if ((int) $document->typedocument->codigo !== 61) {
            throw new \LogicException(
                'Nota De Credito solo soporta Nota De Credito (TipoDTE 61)'
            );
        }

       $this->company  = Company::first();

        //  CodRef  Significado oficial               
        //  ------  --------------------------------- 
        //  1       Anula Documento de Referencia 
        //  2       Corrige Texto                     
        //  3       Corrige Montos  

        $referencia = [
            [
                'NroLinRef' => 1,
                'TpoDocRef' => $document->parent->typedocument->codigo,                  // Factura afecta referenciada
                'FolioRef'  => $document->parent->folio,
                'FchRef'    => $document->parent->fecha,
                'CodRef'    => 1,                   // Anula documento
            ]
        ];


        $dte = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' =>  $document->typedocument->codigo,
                    'Folio'   => 0,
                    'FchEmis' => $document->fecha,                    
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
                    'MntNeto'      => $document->neto,
                    'TasaIVA'      => 19,                   
                    'IVA'          => $document->iva,                   
                    'MntTotal'     => $document->total,                                 
                ],
            ],

            // 🔹 Ítems afectos (IndExe = 0 o ausente)
            'Detalle'      => $this->mapDetails($document),          
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
