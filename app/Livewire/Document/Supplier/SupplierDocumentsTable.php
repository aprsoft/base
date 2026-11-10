<?php

namespace App\Livewire\Documents\Supplier;

use App\Models\Document;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Responsive;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class SupplierDocumentsTable extends PowerGridComponent
{
    public string $tableName = 'supplierDocumentsTable';

    public ?string $condicion = null;
    public ?int    $supplierId = null;

    public function setUp(): array
    {     

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
            PowerGrid::responsive()
                ->fixedColumns('fullName', Responsive::ACTIONS_COLUMN_NAME)
        ];
    }  

    public function datasource(): Builder
    {       

        return Document::query()
            ->join('suppliers', 'suppliers.id', '=', 'documents.supplier_id')
            ->join('typedocuments', 'typedocuments.id', '=', 'documents.typedocument_id')
            ->when($this->condicion, function ($query) {
                $query->where('documents.condicion_documento', $this->condicion);
            })
            ->when($this->supplierId, function ($query) {
                $query->where('documents.supplier_id', $this->supplierId);
            })
            ->whereNotNull('supplier_id')
            ->vigente()
            ->select('documents.*');

        // return Document::query()
        //     ->vigente()
        //     ->whereNotNull('supplier_id')
        //     ->with([
        //         'supplier',
        //         'typedocument',
        //         'reading',
        //         'details', 
        //     ])
        //     ->when($this->condicion, fn ($q) =>
        //         $q->where('condicion_documento', $this->condicion)
        //     )
        //     ->when($this->supplierId, fn ($q) =>
        //         $q->where('supplier_id', $this->supplierId)
        //     );
    }
    
    
    Public function fields(): PowerGridFields
    {
        return PowerGrid::Fields()
            // ->add('id')
            ->add('rut', fn($doc) => $doc->supplier?->rutFormat ?? '')            
            ->add('fullName', fn($doc) => $doc->supplier?->fullName ?? '')
            ->add('tipo', fn($doc) => $doc->typedocument?->abrev ?? '')
            ->add('folio')
            ->add('fecha');
    }

     public function columns(): array
    {
        return [
            // Column::make('ID', 'id')
            // ->searchableRaw('documents.id LIKE ?')
            // ->sortable(),
            Column::make('RUT', 'rut')
                ->sortable()
                ->searchableRaw('suppliers.rut LIKE ?'), 
            Column::make('Razon Social', 'fullName')
                ->searchableRaw("CONCAT_WS(suppliers.nombres, ' ', suppliers.apellido_paterno, ' ', suppliers.apellido_materno) like ?")
                ->sortable(), 
             Column::make('Tipo', 'tipo')
                ->searchableRaw('typedocuments.name LIKE ?')
                ->sortable(), 
            Column::make('Folio', 'folio')->sortable(),            
            Column::make('Fecha', 'fecha')->searchable()->sortable(),
            Column::action('Acciones'),  
        ];
    }
   

    // #[\Livewire\Attributes\On('edit')]
    // public function edit($rowId): void
    // {
    //     $this->js('alert('.$rowId.')');
    // }

    public function actions(Document $document): array
    {
        return [
            Button::add('show')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>')
                ->id()
                ->class('px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700 flex items-center justify-center')
                ->route('documents.show', ['document' => $document->id]),

            
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
