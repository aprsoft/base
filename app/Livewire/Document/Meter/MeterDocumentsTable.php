<?php

namespace App\Livewire\Documents\Meter;

use App\Models\Document;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Responsive;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Illuminate\Support\Number;
use Livewire\Attributes\On;


final class MeterDocumentsTable extends PowerGridComponent
{
    public string $tableName = 'meterDocumentsTable';

    public ?array  $condicion = null;
    public ?string $type      = null;
    public ?string $routeName = null;
    public ?int    $meterId   = null;


   

    public function setUp(): array
    {      
        if ($this->routeName === 'payments.create.meter') {
            $this->showCheckBox();
        }

        

        return [
            
            // PowerGrid::detail()
            //     ->view('tenant.admin.powergrid.detail')
            //     ->showCollapseIcon(),            
            PowerGrid::header()
                ->showToggleColumns()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
            PowerGrid::responsive()
                ->fixedColumns('detalle', Responsive::ACTIONS_COLUMN_NAME)    
             
        ];
    }   

    public function datasource(): Builder
    { 
        return Document::query()
            ->join('meters', 'meters.id', '=', 'documents.meter_id')
            ->join('customers', 'customers.id', '=', 'documents.customer_id')
            ->join('typedocuments', 'typedocuments.id', '=', 'documents.typedocument_id')
            ->join('readings', 'readings.id', '=', 'documents.reading_id')

            ->when($this->condicion, function ($query) {
               
                $query->whereIn('documents.condicion_documento', $this->condicion);
            })
            ->when($this->meterId, function ($query) {
                $query->where('documents.meter_id', $this->meterId);
            })            
            ->whereNotNull('documents.meter_id')
            ->vigente()
            ->select('documents.*');     
        
    }
    
    
    Public function fields(): PowerGridFields
    {
        return PowerGrid::Fields()
            ->add('id')
            ->add('rut', fn($doc) => $doc->meter?->rutFormat ?? '')            
            ->add('razon_social', fn($doc) => $doc->meter?->customer->first()->fullName ?? '')
            ->add('detalle', fn($doc) => $doc->reading?->mes_cobro ?? 'Servicio')
            ->add('tipo', fn($doc) => $doc->typedocument?->abrev ?? '')
            ->add('condicion_documento')
            ->add('folio')
            ->add('fecha')
            ->add('total', fn ($doc) =>Number::currency($doc->total ?? 0, in: 'CLP', locale: 'es_CL'));            

    }

     public function columns(): array
    {
        return [
            Column::make('ID', 'id')
            ->searchableRaw('documents.id LIKE ?')
            ->hidden(isHidden: true, isForceHidden: false)
            ->sortable(),
            Column::make('RUT', 'rut')
                ->sortable()
                ->searchableRaw('meters.rut LIKE ?')
                ->hidden(isHidden: true, isForceHidden: false), 
            Column::make('Razon Social', 'razon_social')
                ->searchableRaw("CONCAT_WS(' ',customers.nombres, ' ', customers.apellido_paterno, ' ', customers.apellido_materno) like ?")
                ->sortable()
                ->hidden(isHidden: true, isForceHidden: false),              
            Column::make('Tipo', 'tipo')
                ->searchableRaw('typedocuments.name LIKE ?')
                ->sortable()
                ->hidden(isHidden: true, isForceHidden: false),  
            Column::make('Condicion', 'condicion_documento')
                ->searchable()
                ->hidden(isHidden: false, isForceHidden: false)
                ->sortable(), 
            Column::make('Folio', 'folio')
                ->searchable()
                ->sortable()
                ->hidden(isHidden: false, isForceHidden: false),            
            Column::make('Fecha', 'fecha')
                ->searchable()
                ->sortable(),
            Column::make('Detalle', 'detalle')
                ->searchableRaw('readings.mes_cobro LIKE ?')  
                ->sortable(), 
             Column::make('Total', 'total')
                ->sortable(),                   
            Column::action('Acciones'),            
        ];
    }   


    public function actions(Document $row): array
    {
        return [
            Button::add('show')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>')
                
                ->class('px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700 flex items-center justify-center')
                ->route('documents.show', ['document' => $row->id]),   
            

           
        ];
    }

    
}
