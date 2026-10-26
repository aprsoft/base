<?php

namespace App\Livewire\User;

use App\Models\User;
use App\Services\User\UserLifecycleService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UserTable extends PowerGridComponent
{
    public string $tableName = 'userTable';

    public function setUp(): array
    {
        // $this->showCheckBox();

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
        Gate::authorize('viewAny', User::class);

        $user = auth('web')->user();

        return User::withTrashed()
            ->where('id', '!=', 1)
            ->when(
                ! $user->hasRole('SuperAdmin', 'web'),
                fn (Builder $query) => $query
                    ->where('id', '!=', 1)
                    ->whereDoesntHave(
                        'roles',
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

           /** Example of custom column using a closure **/
            ->add('name_lower', fn (user $model) => strtolower(e($model->name)))

            ->add('email')

            ->add('estado', function ($tenant) {
            if ($tenant->trashed()) {
                return '<span class="inline-flex items-center gap-1 text-red-600 font-medium">
                            <span class="text-lg">✕</span>
                            Suspendido
                        </span>';
            }

            return '<span class="inline-flex items-center gap-1 text-green-600 font-medium">
                        <span class="text-lg">✓</span>
                        Activo
                    </span>';
            });
            // ->add('created_at_formatted', fn (user $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));

            // ->add('email')
            // ->add('created_at_formatted', fn (user $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Estado', 'estado'),


            // Column::make('Created at', 'created_at_formatted', 'created_at')
            //     ->sortable()
            //     ->searchable(),

            Column::action('Action')
        ];
    }

    // public function filters(): array
    // {
    //     return [
    //         Filter::inputText('name')->operators(['contains']),
    //         Filter::inputText('email')->operators(['contains']),
    //         Filter::datetimepicker('created_at'),
    //     ];
    // }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
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

    #[On('user-table-request-restore')]
    public function confirmRestore(int $id): void
    {
        $user = User::withTrashed()->findOrFail($id);
        Gate::authorize('restore', $user);

        $this->dispatch(
            'open-confirm-modal',
            id: $user->id,
            name: $user->name,
            confirmEvent: 'user-table-restore-confirmed',
            title: 'Restaurar usuario',
            message: '¿Confirmas que deseas restaurar el acceso de esta cuenta?',
            warning: 'La cuenta recuperará sus roles y permisos anteriores.',
            confirmLabel: 'Sí, restaurar'
        );
    }

    #[On('user-table-restore-confirmed')]
    public function restore(int $id, UserLifecycleService $lifecycle): void
    {
        $lifecycle->restore(auth('web')->user(), $id);
        session()->flash('success', 'Usuario restaurado correctamente.');
        $this->redirectRoute('users.index');
    }

    public function actionRules(): array
    {
        return [
            Rule::button('show')
                ->when(fn (User $row) =>
                    Gate::denies('view', $row)
                )
                ->hide(),

            Rule::button('edit')
                ->when(fn (User $row) =>
                    Gate::denies('manage', $row)
                )
                ->hide(),
        ];
    }
}
