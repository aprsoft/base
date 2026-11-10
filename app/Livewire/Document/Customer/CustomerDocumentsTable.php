<?php

namespace App\Livewire\Document\Customer;

use App\Models\Customer;
use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\ColumnToggle;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Responsive;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;


final class CustomerDocumentsTable extends PowerGridComponent
{
    public string $tableName = 'customerDocumentsTable';

    public ?string  $condition   = null;
    public ?string  $type        = null;
    public Customer $customer;
    public ?bool    $boolPayment = true;

    public string   $sortField = 'id';
    public string   $sortDirection = 'desc';


    
    public function setUp(): array
    {  

        return [
                  
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
            ->join('customers', 'customers.id', '=', 'documents.customer_id')
            ->join('typedocuments', 'typedocuments.id', '=', 'documents.typedocument_id')
            ->join('readings', 'readings.id', '=', 'documents.reading_id')
            ->where('documents.customer_id', $this->customer->id)
            ->when($this->condition, function ($query) {
                $query->where('documents.condicion_documento', $this->condition);
            })                    
            ->whereNotNull('documents.customer_id')
            ->vigente()
            ->select('documents.*');     
        
    }
    
    
    Public function fields(): PowerGridFields
    {
        return PowerGrid::Fields()
            ->add('id')
            ->add('rut', fn($doc) => $doc->customer?->rutFormat ?? '')            
            // ->add('fullName', fn($doc) => $doc->customer?->fullName ?? '')
            ->add('detalle', fn($doc) => $doc->reading?->mes_cobro ?? 'Servicio')
            ->add('tipo', fn($doc) => $doc->typedocument?->abrev ?? '')
            ->add('condicion_documento')
            ->add('folio')
            ->add('comprobante',fn($doc)=>$this->paymentLink($doc)) 
            ->add('fecha')
            ->add('total', fn ($doc) =>Number::currency($doc->total ?? 0, in: 'CLP', locale: 'es_CL'));             
;
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
                ->searchableRaw('customers.rut LIKE ?')
                ->hidden(isHidden: true, isForceHidden: false), 
            // Column::make('Razon Social', 'fullName')
            //     ->searchableRaw("CONCAT_WS(' ',customers.nombres, customers.apellido_paterno, customers.apellido_materno) 
            //         like  CONCAT('%', REPLACE(?, ' ', '%'), '%')")
            //     // ->hidden(isHidden: true, isForceHidden: false)
            //     ->sortable(),            
            Column::make('Tipo', 'tipo')
                ->searchableRaw('typedocuments.name LIKE ?')
                ->sortable()
                ->hidden(isHidden: false, isForceHidden: false),  
            Column::make('Condicion', 'condicion_documento')
                ->searchable()
                ->hidden(isHidden: false, isForceHidden: false)
                ->sortable(), 
            Column::make('Folio', 'folio')
                ->searchable()
                ->sortable()
                ->hidden(isHidden: false, isForceHidden: false), 
            $this->paymentColumn($this->boolPayment),
           
            Column::make('Fecha', 'fecha')
                ->searchable()
                ->sortable(),
            Column::make('Detalle', 'detalle')
                ->searchableRaw('readings.mes_cobro LIKE ?')  
                ->sortable(),
            Column::make('Total', 'total')
                ->searchableRaw('readings.mes_cobro LIKE ?')  
                ->sortable(),                    
            Column::action('Acciones'),
            
        ];
    }    

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
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

    protected function paymentLink(Document $doc)
    {
        $payment = $doc->payment()->first();

            if (!$payment) {
                return '';
            }

            return sprintf(
                '<a href="%s" class="text-blue-600 hover:underline font-semibold">
                    %s
                </a>',
                route('payments.show', $payment->id),
                $payment->id
            );

    }
    
    protected function paymentColumn(bool $boolPayment)
    {
        return $boolPayment
            ? Column::make('N° Comprobante', 'comprobante')
            : null;
    }

 
}
