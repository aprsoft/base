<?php

namespace App\Services\Document\DocumentType;

use App\Models\Benefit;
use App\Models\Stretche;
use App\Models\Detail;
use App\Models\Document;
use App\Models\ErrorLog;
use App\Models\Reading;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\Document\DocumentType\BaseDocument;

class ConsumptionDocument extends BaseDocument
{
    private $request;

    // public function __construct( $request$request)
    // {
    //     $this->request = $request;
    // }

    public function create($data): Document
    {                       
        try {
            // Obtiene o crea la lectura actual
            $lastReading = Reading::ensureCurrentForMeter($data['meter_id']);

            $lastReading->update([
                'condicion_lectura' => 'PROCESADA',
            ]);
           

            $carbon = Carbon::parse($data['fecha'])->locale('es');

            $mes_cobro = strtoupper(
                $carbon->monthName . ' (' . $carbon->year . ')'
            );

            $reading = Reading::create([
                'meter_id'          => $data['meter_id'],
                'lectura'           => $data['lectura_nueva'] ?? 0,
                'mes_cobro'         => $mes_cobro,
                'estado_lectura'    => 'VIGENTE',
                'condicion_lectura' => 'EN PROCESO',
                'validate'          => 'VALIDADA',
                'fecha'             => $data['fecha'],
            ]);

            
            $data = collect($data)->merge([
                'reading_id' => $reading->id,
                'fecha'      => $reading->fecha,
                'user_id'    => $data['user_id'],
            ]);

            $document = parent::createDocumentBase($data);

            $document->update(['typedocument_id' => 12,'condicion_documento'=>'PENDIENTE']);

            $consumption = $reading->lectura - $lastReading->lectura;

            $stretches = $this->calcularTramos($consumption);

            $this->createConsumptionDetails($document, $stretches);
            $this->createBenefitDetails($document); 

            $document->update([
                'monto_neto'=>$document->neto,
                'monto_iva'=>$document->iva,
                'monto_total'=>$document->total
                ]);
            
            return $document;
        
        } catch (\Throwable $e) {

            ErrorLog::create([        
                    'message'   => $e->getMessage(),        
                    'exception' => class_basename($e),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),        
            ]);

            throw $e;
           
        }
            
    }

    protected function createConsumptionDetails(Document $document, array $stretches): void
    {
        
        Detail::create([
            'document_id'        => $document->id,
            'estado_detalle'     => 'VIGENTE',
            'service_id'         => 12,
            'stretche_id'       => null,
            'cantidad'           => 1,
            'valor'              => 4320,
            'neto'               => 4320,
            'iva'                => 0,
            'total'              => 4320,
            'detalle'            => 'CARGO FIJO',
        ]);

        if (!empty($stretches) && is_array($stretches)) {
            foreach ($stretches as $stretche) {
                Detail::create([
                    'document_id'        => $document->id,
                    'estado_detalle'     => 'VIGENTE',
                    'service_id'         => 12,
                    'stretche_id'        => $stretche['tramo_id'],
                    'cantidad'           => $stretche['metros'],
                    'valor'              => $stretche['valor'],
                    'neto'               => $stretche['valor'] * $stretche['metros'],
                    'iva'                => 0,
                    'total'              => $stretche['valor'] * $stretche['metros'],
                    'detalle'            => $stretche['detalle'],
                ]);
            }
        }
                  


    }

    protected static function createBenefitDetails(Document $document): void
    {
        $benefit = Benefit::activeBenefitForMeter($document->meter_id);

        if ($benefit === null) {
            return;
        }

        $total = $document->sumaTotal();

        switch ($benefit->condicion_subsidio) {
            case 'TOTAL':
                self::createBaseBenefitDetails(
                    $document,
                    $total * -1,
                    'SUBSIDIO TOTAL'
                );
                break;

            case 'MONTO':
                $monto = $benefit->monto;
                $descuento = min($total, $monto);

                self::createBaseBenefitDetails(
                    $document,
                    $descuento * -1,
                    "SUBSIDIO POR MONTO: MÁXIMO $monto"
                );
                break;
        }
    }

    protected static function createBaseBenefitDetails(
        Document $doc,
        float $valor,
        string $descripcion
    ): void {
        Detail::create([
            'document_id'    => $doc->id,
            'estado_subsidio'=> 'VIGENTE',
            'service_id'     => 11,
            'cantidad'       => 1,
            'valor'          => $valor,
            'neto'           => $valor,
            'iva'            => 0,
            'total'          => $valor,
            'detalle'        => $descripcion,
        ]);
    }

    protected static function calcularTramos(int $consumption): array
    {
        $result = [];

        $stretches = Stretche::vigentes()->get();

        foreach ($stretches as $stretche) {

            if ($consumption < $stretche->inicio) {
                break;
            }

            $limiteSuperior = min($consumption, $stretche->termino);
            $metrosEnTramo  = $limiteSuperior - ($stretche->inicio === 0 ? $stretche->inicio: $stretche->inicio - 1) ;
                        // dd($metrosEnTramo);


            if ($metrosEnTramo > 0) {
                $result[] = [
                    'tramo_id' => $stretche->id,
                    'inicio'   => $stretche->inicio,
                    'termino'  => $limiteSuperior,
                    'metros'   => $metrosEnTramo,
                    'valor'    => $stretche->neto,
                    'total'    => $metrosEnTramo * $stretche->valor,
                    'detalle'  => Stretche::findOrFail($stretche->id)->name,
                ];
            }
        }

       

        return $result;
    }
}
