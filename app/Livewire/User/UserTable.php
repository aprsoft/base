<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UserTable extends PowerGridComponent
{
    public string $tableName = 'userTable';

    public function setUp(): array
    {
        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()->showPerPage()->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        Gate::authorize('viewAny', User::class);

        $actor = auth('web')->user();

        return User::withTrashed()
            ->where('id', '!=', 1)
            ->when(
                ! $actor->hasRole('SuperAdmin', 'web'),
                fn (Builder $query) => $query->whereDoesntHave('roles',
                    fn (Builder $roles) => $roles
                        ->where('name', 'SuperAdmin')
                        ->where('guard_name', 'web')
                )
            );
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
            ->add('email')
            ->add('estado', static fn (User $user) => $user->trashed()
                ? '<span class="inline-flex items-center gap-1 text-red-600 font-medium">✕ Suspendido</span>'
                : '<span class="inline-flex items-center gap-1 text-green-600 font-medium">✓ Activo</span>');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Name', 'name')->sortable()->searchable(),
            Column::make('Email', 'email')->sortable()->searchable(),
            Column::make('Estado', 'estado'),
            Column::action('Action'),
        ];
    }

    public function actions(User $row): array
    {
        return [
            Button::add('show')
                ->icon('default-eye')
                ->class('px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700')
                ->route('users.show', ['user' => $row->id]),

            Button::add('edit')
                ->icon('default-pencil')
                ->class('px-2 py-1 bg-gray-600 text-white rounded hover:bg-gray-700')
                ->route('users.edit', ['user' => $row->id]),
        ];
    }

    public function actionRules(): array
    {
        return [
            Rule::button('show')
                ->when(fn (User $row) => Gate::denies('view', $row))
                ->hide(),

            Rule::button('edit')
                ->when(fn (User $row) => Gate::denies('manage', $row))
                ->hide(),
        ];
    }
}