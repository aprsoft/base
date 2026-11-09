<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Responsive;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Facades\Rule;



final class CustomerTable extends PowerGridComponent
{
    public string $tableName = 'customerTable';
    public ?string $routeName = null;
    

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
                ->fixedColumns('razon_social')
        ];        
    }

    public function datasource(): Builder
    {
        return Customer::query();
    }
  
    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()            
            ->add('fullName') //razon_social es un accesor
            ->add('tipo_cliente')
            ->add('condicion_cliente');
    }

    public function columns(): array
    {
        return [
            
            Column::make('Razon Social','fullName')
                ->searchableRaw("
                        CONCAT_WS(
                            ' ',
                            customers.nombres,
                            customers.apellido_paterno,
                            customers.apellido_materno
                        ) LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')
                    "),            
            Column::make('Tipo','tipo_cliente')
                ->hidden(isHidden: true, isForceHidden: false),
            Column::make('Condicion','condicion_cliente')
                ->hidden(isHidden: true, isForceHidden: false),
            Column::action('Action'),
        ];
    }    

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(Customer $row): array
    {
        return [
            Button::add('show')
                ->icon('default-eye')               
                ->class('px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700 flex items-center justify-center')
                ->route('customers.show', ['customer' => $row->id]), 
            Button::add('edit')
                ->icon('default-pencil')               
                ->class('px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 flex items-center justify-center')
                ->route('customers.edit', ['customer' => $row->id]),   
            Button::add('documents')
                ->icon('default-files')     
                ->class('px-2 py-1 bg-gray-600 text-white rounded hover:bg-gray-600 flex items-center justify-center')
                ->route('documents.customer.index', ['customer' => $row->id]),
            Button::add('toPay')
                ->icon('default-cash') 
                ->class('px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 flex items-center justify-center')
                ->route('payments.create.customer', ['customer' => $row->id])
        ];
    }

    
    public function actionRules(): array
    {   
       
       return [
            // Hide button edit for ID 1
            Rule::button('show')
                ->hide()
                ->when(fn() => $this->routeName === 'payments.index.create'),                
            Rule::button('documents')
                ->hide()
                ->when(fn() => $this->routeName === 'payments.index.create'),     
            Rule::button('toPay')
                ->hide()
                ->when(fn() => $this->routeName === 'customers.index'),  
            Rule::button('edit')
                ->hide()
                ->when(fn() => $this->routeName === 'payments.index.create'),                
        ];
    }
    
}
