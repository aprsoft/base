<?php

namespace App\Livewire\Permission;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Spatie\Permission\Models\Permission;
use App\Services\Permission\DeletePermissionService;
use App\Services\Central\ErrorLog\ErrorLogger;
use Illuminate\Database\QueryException;
use DomainException;
use Throwable;

final class PermissionTable extends PowerGridComponent
{
    public string $tableName = 'permissionTable';

    public function setUp(): array
    {
        if ($this->isRoleForm()) {
            $this->showCheckBox();
        }

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
        abort_unless(auth('web')->user()?->hasRole('SuperAdmin', 'web'), 403);

        return Permission::query()
            ->where('guard_name', 'web');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name');
    }

    public function columns(): array
    {
        $columns = [
            Column::make('Id', 'id')
                ->sortable(),

            Column::make('Permisos', 'name')
                ->sortable()
                ->searchable(),
        ];

        if (! $this->isRoleForm()) {
            $columns[] = Column::action('Acciones');
        }

        return $columns;
    }

    #[On('permission-destroy-confirmed')]
    public function destroy(
        DeletePermissionService $deletePermission,
        ErrorLogger $errorLogger,
        int $id
    ): void {
        abort_unless(auth('web')->user()?->hasRole('SuperAdmin', 'web'), 403);

        try {
            $permissionName = $deletePermission->delete($id);

            session()->flash(
                'success',
                sprintf(
                    'El permiso "%s" fue eliminado correctamente.',
                    $permissionName
                )
            );
        } catch (DomainException $exception) {
            $errorLogger->report(
                $exception,
                [
                    'operation' => 'permission.destroy',
                    'permission_id' => $id,
                    'error_type' => 'business',
                ]
            );

            session()->flash(
                'error',
                $exception->getMessage()
            );
        } catch (QueryException $exception) {
            $errorLogger->report(
                $exception,
                [
                    'operation' => 'permission.destroy',
                    'permission_id' => $id,
                    'error_type' => 'database',
                ]
            );

            session()->flash(
                'error',
                'Ocurrió un error de base de datos al eliminar el permiso.'
            );
        } catch (Throwable $exception) {
            $errorLogger->report(
                $exception,
                [
                    'operation' => 'permission.destroy',
                    'permission_id' => $id,
                    'error_type' => 'unexpected',
                ]
            );

            session()->flash(
                'error',
                'Ocurrió un error inesperado al eliminar el permiso.'
            );
        }

        $this->redirectRoute('permissions.index');
    }

    public function actions(Permission $row): array
    {
        if ($this->isRoleForm()) {
            return [];
        }

        return [
            Button::add('show')
                ->icon('default-eye')
                ->class(
                    'px-2 py-1 bg-green-600 text-white rounded ' .
                    'hover:bg-green-700 flex items-center justify-center'
                )
                ->route(
                    'permissions.show',
                    ['permission' => $row->id]
                ),

            Button::add('edit')
                ->icon('default-pencil')
                ->class(
                    'px-2 py-1 bg-gray-600 text-white rounded ' .
                    'hover:bg-gray-700 flex items-center justify-center'
                )
                ->route(
                    'permissions.edit',
                    ['permission' => $row->id]
                ),

            Button::add('destroy')
                ->icon('default-trash')
                ->class(
                    'px-2 py-1 bg-red-600 text-white rounded ' .
                    'hover:bg-red-700 flex items-center justify-center'
                )
                ->dispatch(
                    'open-confirm-modal',
                    [
                        'id' => $row->id,
                        'name' => $row->name,
                        'confirmEvent' => 'permission-destroy-confirmed',
                        'title' => 'Eliminar permiso',
                        'message' => '¿Está seguro de que desea eliminar este permiso?',
                        'warning' => 'Esta acción no se puede deshacer.',
                    ]
                ),
        ];
    }

    private function isRoleForm(): bool
    {
        return in_array(
            Route::currentRouteName(),
            [
                'roles.create',
                'roles.edit',
            ],
            true
        );
    }
}