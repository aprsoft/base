<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\Meter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class OverdueDocumentsTable extends PowerGridComponent
{
    public string $tableName = 'overdueDocumentsTable';

    /* ==========================
     |  SETUP
     |==========================*/
    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    /* ==========================
     |  DATASOURCE
     |==========================*/
    // public function datasource(): Builder
    // {


    //     return Meter::query()
    //         ->leftJoin('documents', 'meters.id','documents.meter_id')           
    //         ->where('documents.condicion_documento', 'MOROSO')
    //         ->groupBy('meters.numero_medidor')
    //         ->select(
    //             'meters.numero_medidor','meters.id',
    //             DB::raw('COUNT(documents.id) as total_documentos'),
    //             DB::raw("
    //                 CASE
    //                     WHEN COUNT(documents.id) > 3
    //                     THEN 'CORTE EN TRÁMITE'
    //                     ELSE 'SIN CORTE'
    //                 END as estado_corte
    //             ")
    //         );
    // }

    public function datasource(): Builder
    {
        return Meter::query()        
            ->join('documents', 'meters.id', '=', 'documents.meter_id')
            ->join('customers', 'customers.id', '=', 'documents.customer_id')
            ->where('documents.condicion_documento', 'MOROSO')
            ->groupBy(
                'meters.id',
                'meters.numero_medidor',
                'customers.rut',
                
            )
            ->select(
                'meters.id',
                'meters.numero_medidor',
                'customers.rut',
                
                DB::raw('COUNT(documents.id) as total_documentos'),
                DB::raw("
                    CASE
                        WHEN COUNT(documents.id) > 3
                        THEN 'CORTE EN TRÁMITE'
                        ELSE 'SIN CORTE'
                    END as estado_corte
                ")
            );
    }

    /* ==========================
     |  FIELDS
     |==========================*/
    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('rut', fn ($meter) => $meter->customer()->first()->rutFormat)
            ->add('razon_social', fn ($meter) => $meter->customer()->first()->fullName)
            ->add('numero_medidor')
            ->add('total_morosos')
            ->add('estado_corte');
          
    }

    /* ==========================
     |  COLUMNS
     |==========================*/
    public function columns(): array
    {
        return [
            Column::make('RUT', 'rut')
                ->searchableRaw('customers.rut LIKE ?'),
            Column::make('Razon Social', 'razon_social')
                ->searchableRaw("CONCAT_WS(' ',customers.nombres, customers.apellido_paterno, customers.apellido_materno) 
                    like  CONCAT('%', REPLACE(?, ' ', '%'), '%')"),            
            Column::make('Medidor', 'numero_medidor'),
            Column::make('N° Documentos Morosos', 'total_documentos')
                ->sortable(),
            Column::make('Estado', 'estado_corte'),

            // Column::make('Total', 'total_format'),estado_corte
        ];
    }
}
