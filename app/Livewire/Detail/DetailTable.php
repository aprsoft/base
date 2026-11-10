<?php

namespace App\Livewire\Detail;

use App\Models\Detail;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class DetailTable extends PowerGridComponent
{
    public string $tableName = 'detailTable';

    public int $documentId;
   

    public function setUp(): array
    {

        return [
            // PowerGrid::header()
            //     ->showSearchInput(),
            PowerGrid::footer()
            //     ->showPerPage()
            //     ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Detail::query()
            ->where('document_id', $this->documentId);
    }    

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
        ->add('id')
        ->add('detalle')
        ->add('cantidad')
        ->add('neto', fn ($detail) =>Number::currency($detail->neto ?? 0, in: 'CLP', locale: 'es_CL')) 
        ->add('iva', fn ($detail) =>Number::currency($detail->iva ?? 0, in: 'CLP', locale: 'es_CL')) 
        ->add('total', fn ($detail) =>Number::currency($detail->total ?? 0, in: 'CLP', locale: 'es_CL'));             

    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Detalle', 'detalle'),
            Column::make('Cantidad', 'cantidad'),
            Column::make('Neto',  'neto')
                ->withSum('Neto', header: false, footer: true),
            Column::make('Iva',  'iva')
                ->withSum('Iva', header: false, footer: true),
            Column::make('Total',  'total')
                ->withSum('Total', header: false, footer: true),
    
        ];
    }

 

    public function summarizeFormat(): array
{
    return [
        'total.{sum}' => fn ($value) =>Number::currency($value, in: 'CLP', locale: 'es_CL'),
        'iva.{sum}' => fn ($value) =>Number::currency($value, in: 'CLP', locale: 'es_CL'),
        'neto.{sum}' => fn ($value) =>Number::currency($value, in: 'CLP', locale: 'es_CL'),
    ];
}


}
