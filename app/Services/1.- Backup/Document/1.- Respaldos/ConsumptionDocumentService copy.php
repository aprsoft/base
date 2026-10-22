<?php

namespace App\Services\Document;

use App\Models\Benefit;
use App\Models\ConsumptionTier;
use App\Models\Detail;
use App\Models\Document;
use App\Models\Reading;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConsumptionDocumentService extends BaseDocumentService
{
    public function create(array $data): Document
    {
        return DB::transaction(function () use ($data) {   

            $document = parent::createDocumentBase($data);            
            $document->update(['typedocument_id' => 12]); 
            
            $lastReading = Reading::lastReadingForMeterObj($data['meter_id']);

            if ($lastReading === null) {
                Reading::initialMeterReading($data['meter_id']);
                $lastReading = Reading::lastReadingForMeterObj($data['meter_id']);
            }

            $lastReading->update(['condicion_lectura'  => 'PROCESADA']);
           
            $reading = Reading::create([
                        'meter_id'           => $data['meter_id'],
                        'lectura'            => $data['lectura'] ?? 0,
                        'mes_cobro'          => strtoupper(Carbon::parse($data['fecha'])->locale('es')->monthName),
                        'estado_lectura'     => 'VIGENTE',
                        'condicion_lectura'  => 'EN PROCESO',
                        'validate'           => 'VALIDADA',
                        'fecha'              => Carbon::parse($data['fecha'])->format('Y-m-d'),
                    ]);

            $consumption=  $reading->lectura - $lastReading->lectura; 
           

            $tiers = $this->calcularTramos($consumption); 
 
			$this->createConsumptionDetails($document, $tiers);  
            $this->createBenefitDetails($document); 
            
            //dd($document->details);

            return $document;
        });
    }

    protected function createConsumptionDetails(Document $document, array $tiers)
    {  
        Detail::create([
                'document_id'           => $document->id,
                'estado_detalle'        => 'VIGENTE',
                'service_id'            => 12,
                'consumptionTier_id'    => null,
                'cantidad'              => 1,
                'valor'                 => 4320,
                'neto'                  => 4320,
                'iva'                   => 0,
                'total'                 => 4320,
                'detalle'               => 'CARGO FIJO',
        ]);           
       

        if (!empty($tiers) && is_array($tiers)) {
            foreach ($tiers as $tier) {
                
                $details = Detail::create([
                    'document_id'           => $document->id,
                    'estado_detalle'        => 'VIGENTE',
                    'service_id'            => 12,
                    'consumptionTier_id'    => $tier['tramo_id'],
                    'cantidad'              => $tier['metros'],
                    'valor'                 => $tier['valor'],
                    'neto'                  => $tier['valor'] * $tier['metros'],
                    'iva'                   => 0,
                    'total'                 => $tier['valor'] * $tier['metros'],
                    'detalle'               => $tier['detalle'],

                ]);
            }           
        }       
    }

    protected static function createBenefitDetails($document)
    {

        $benefit = Benefit::activeBenefitForMeter($document->meter_id);

        if ($benefit === null) {
            return;
        }

        $total = $document->sumaTotal();

        switch ($benefit->condicion_subsidio) {
            case 'TOTAL':
                self::crearDetalleSubsidio($document, $total * -1, 'SUBSIDIO TOTAL');
                break;

            case 'MONTO':
                $monto = $benefit->monto;
                $descuento = min($total, $monto);
                self::crearDetalleSubsidio($document, $descuento * -1, "SUBSIDIO POR MONTO: MÁXIMO $monto");
                break;
        }
    }

    protected static function crearDetalleSubsidio($doc, $valor, $descripcion)
    {
        Detail::create([
            'document_id' => $doc->id,
            'estado_subsidio' => 1,
            'service_id' => 11,
            'cantidad' => 1,
            'valor' => $valor,
            'neto' => $valor,
            'iva' => 0,
            'total' => $valor,
            'detalle' => $descripcion,
        ]);
    }

    protected static function calcularTramos(int $consumption): array
    {
        $result = [];

        $tiers = ConsumptionTier::vigentes()->get();        

        foreach ($tiers as $tier) {

            // Si el consumo no alcanza este tramo
            if ($consumption < $tier->inicio) {
                break;
            }

            // Límite real a cobrar en este tramo
            $limiteSuperior = min($consumption, $tier->termino);

            $metrosEnTramo = $limiteSuperior - $tier->inicio + 1;

            if ($metrosEnTramo > 0) {
                $result[] = [
                    'tramo_id' => $tier->id,
                    'inicio'   => $tier->inicio,
                    'termino'  => $limiteSuperior,
                    'metros'   => $metrosEnTramo,
                    'valor'    => $tier->neto,
                    'total'    => $metrosEnTramo * $tier->valor,
                    'detalle'  => ConsumptionTier::findOrFail($tier->id)->name,
                ];
            }
        }

        return $result;
    }
}
