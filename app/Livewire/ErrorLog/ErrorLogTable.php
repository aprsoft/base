<?php

namespace App\Livewire\ErrorLog;


use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Illuminate\Support\Str;
use App\Models\ErrorLog;
use Illuminate\Support\Facades\Gate;

final class ErrorLogTable extends PowerGridComponent
{
    public string $tableName = 'errorLogTable';

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
        Gate::authorize('viewAny', ErrorLog::class);
        return ErrorLog::query()
            ->orderByDesc('created_at');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('tenant_id')
            ->add('message_preview', function (ErrorLog $errorLog) {
                return e(
                    Str::limit(
                        $errorLog->message ?: '-',
                        120
                    )
                );
            })
            ->add('file_preview', function (ErrorLog $errorLog) {
                return e(
                    Str::limit(
                        $errorLog->file ?: '-',
                        80
                    )
                );
            })
            ->add('line')
            ->add('created_at')
            ->add('created_at_formatted', function (ErrorLog $errorLog) {
                return $errorLog->created_at?->format('d/m/Y H:i:s') ?? '-';
            });
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->searchable(),

            Column::make(
                'Inquilino',
                'tenant_id'
            )
                ->sortable()
                ->searchable(),

            Column::make(
                'Mensaje',
                'message_preview',
                'message'
            )
                ->searchable(),

            // Column::make(
            //     'Archivo',
            //     'file_preview',
            //     'file'
            // )
            //     ->searchable(),

            // Column::make('Línea', 'line'),

            Column::make(
                'Fecha',
                'created_at_formatted',
                'created_at'
            )
                ->sortable(),

            Column::action('Acciones'),
        ];
    }

    public function actions(ErrorLog $row): array
    {
        return [
            Button::add('show')
                ->icon('default-eye')
                ->class(
                    'px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700 flex items-center justify-center'
                )
                ->route('errors.show',['errorLog' => $row->id]
                ),
        ];
    }
}