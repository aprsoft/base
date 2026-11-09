<?php

namespace App\Livewire\Customers;

use App\Models\Document;
use Freshwork\ChileanBundle\Rut;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Illuminate\Support\Number;


final class CustomerOverdueTable extends PowerGridComponent
{
    public string $tableName = 'customerOverdueTable';

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

    public function datasource(): Builder
    {
        return Document::join('customers', 'customers.id', '=', 'documents.customer_id')
            ->join('details', 'details.document_id', '=', 'documents.id')
            ->where('documents.condicion_documento', 'moroso')
            ->groupBy(
                'customers.id',
                'customers.nombres',
                'customers.apellido_paterno',
                'customers.apellido_materno',
                'customers.rut',
            )
            ->select([
                'customers.id as id',
                'customers.rut',
                // 'customers.id as customer_id',
                DB::raw("
                    CONCAT_WS(
                        ' ',
                        customers.nombres, ' ',
                        customers.apellido_paterno, ' ',
                        customers.apellido_materno
                    ) as razon_social
                "),
                DB::raw('COUNT(DISTINCT documents.id) as documentos_morosos'),
                DB::raw('SUM(details.total) as monto_moroso_total'),
            ]);
    }

   

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
        ->add('rut', fn($row) =>Rut::parse($row->rut)->format())  
        ->add('razon_social')
        ->add('documentos_morosos')
        ->add('monto_moroso_total',fn ($detail) =>Number::currency($detail->total ?? 0, in: 'CLP', locale: 'es_CL'));
    }

    public function columns(): array
    {
        return [
            Column::make('RUT', 'rut')
            ->sortable()
            ->searchableRaw('customers.rut LIKE ?'), 
        Column::make('Razon Social', 'razon_social')
            ->sortable()
            ->searchable(),
        Column::make('Documentos morosos', 'documentos_morosos')
            ->sortable(),

        Column::make('Monto moroso', 'monto_moroso_total')
            ->sortable(),
        Column::action('Acciones'),
    ];
       
    }

    public function filters(): array
    {
        return [
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
            Button::add('show-files')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-white"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 3h6l4 4v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 3v4a2 2 0 002 2h4" />
                        </svg>
                        ')
               
                ->class('px-2 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 flex items-center justify-center')
                // ->route('documents.customer.overdue', ['supplier' => $row->id]), 
                // ->route('documents.customer.overdue'), 
                ->route('documents.customer.overdue',['customer' => $row->id]), 

        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
