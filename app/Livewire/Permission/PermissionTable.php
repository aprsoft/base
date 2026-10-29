<?php

declare(strict_types=1);

namespace App\Livewire\Permission;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Spatie\Permission\Models\Permission;

final class PermissionTable extends PowerGridComponent
{
    public string $tableName = 'permissionTable';

    public function setUp(): array
    {
        if ($this->isRoleForm()) {
            $this->showCheckBox();
        }

        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()->showPerPage()->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        Gate::authorize('viewAny', Permission::class);

        return Permission::query()
            ->where('guard_name', 'web')
            ->withExists('roles');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('roles_exists');
    }

    public function columns(): array
    {
        $columns = [
            Column::make('Id', 'id')->sortable(),
            Column::make('Permisos', 'name')
                ->sortable()
                ->searchable(),
        ];

        if (! $this->isRoleForm()) {
            $columns[] = Column::action('Acciones');
        }

        return $columns;
    }

    public function actions(Permission $row): array
    {
        return [
            Button::add('show')
                ->icon('default-eye')
                ->class('px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700')
                ->route('permissions.show', ['permission' => $row->id]),

            Button::add('edit')
                ->icon('default-pencil')
                ->class('px-2 py-1 bg-gray-600 text-white rounded hover:bg-gray-700')
                ->route('permissions.edit', ['permission' => $row->id]),
        ];
    }

    public function actionRules(): array
    {
        return [
            Rule::button('show')
                ->when(fn (Permission $row): bool =>
                    $this->isRoleForm() || Gate::denies('view', $row)
                )
                ->hide(),

            Rule::button('edit')
                ->when(fn (Permission $row): bool =>
                    $this->isRoleForm()
                    || (bool) $row->roles_exists
                    || Gate::denies('update', $row)
                )
                ->hide(),
        ];
    }

    private function isRoleForm(): bool
    {
        return in_array(
            Route::currentRouteName(),
            ['roles.create', 'roles.edit'],
            true
        );
    }
}